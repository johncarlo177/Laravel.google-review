<?php

namespace App\Support\CompatibleSVG\Processors;

use App\Support\QRCodeProcessors\LogoProcessor as LegacyLogoProcessor;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;
use Imagick;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\SVGNode;
use Throwable;

class LogoProcessor extends BaseProcessor
{
    use WriteLogs;

    const ID_LOGO = 'logo-image';

    protected function legacy(): LegacyLogoProcessor
    {
        return LegacyLogoProcessor::getInstance($this->qrcode(), $this->svg());
    }

    protected function shouldProcess()
    {
        // return $this->legacy()->hasLogo();
        return false;
    }

    protected function process()
    {
        try {
            $imagick = $this->legacy()->makeImagickLogoImage();

            $this->addImageNode($imagick);

            $result = $this->pipe([
                [$this, 'centralize'],
                [$this, 'translateToUserSpecifiedPosition'],
            ], $this->logoNode());

            $this->doc()->addChild($result);

            list($cx, $cy) = $this->getQRCodeCenterPoint();

            $this->s->crossAt($this->svg(), 2, $cx, $cy);
            //
        } catch (Throwable $th) {
            $this->logErrorf("%s\n%s", $th->getMessage(), $th->getTraceAsString());
        }
    }

    protected function getQRCodeCenterPoint()
    {
        /**
         * @var SVGNode
         */
        $qrcodeBackground = $this->doc()->getElementById('qrcode-background');

        $x = $qrcodeBackground->getAttribute('x');
        $y = $qrcodeBackground->getAttribute('y');

        $w = $qrcodeBackground->getAttribute('width');
        $h = $qrcodeBackground->getAttribute('height');

        $cx = $x + $w / 2;
        $cy = $y + $h / 2;

        $getNumbers = function ($transform) {
            // Transform comma to white space
            $transform = str_replace(',', ' ', $transform);

            // Remove duplicate white spaces
            $transform = preg_replace('/\s+/', ' ', $transform);

            // Keep only numbers and whitespaces
            $transform = preg_replace('/[^\d\. ]/', '', $transform);

            // Split by whitespace
            $transform = explode(' ', $transform);

            return $transform;
        };

        /**
         * @var SVGNode
         */
        $node = $qrcodeBackground->getParent();

        do {
            $transform = $node->getAttribute('transform');

            $numbers = $getNumbers($transform);

            /**
             * We suppose each transform attribute has either scale
             * or translate function
             */

            if (preg_match('/translate/', $transform)) {
                $cx += $numbers[0];
                $cy += $numbers[1];
            }

            if (preg_match('/scale/', $transform)) {
                $cx *= $numbers[0];
                $cy *= $numbers[0];
            }
        } while ($node = $node->getParent());

        return [$cx, $cy];
    }

    protected function pipe($actions, $initial)
    {
        return array_reduce($actions, function ($result, $action) {
            return call_user_func($action, $result);
        }, $initial);
    }

    protected function centralize(SVGNode $node)
    {
        list($cx, $cy) = $this->getQRCodeCenterPoint();

        $group = new SVGGroup;

        $group->addChild($node);

        $group->setAttribute(
            'transform',
            sprintf(
                'translate(%s, %s)',
                $cx - $node->getAttribute('width') / 2,
                $cy - $node->getAttribute('height') / 2,
            )
        );

        return $group;
    }

    protected function translateToUserSpecifiedPosition(SVGNode $node)
    {
        $group = new SVGGroup();

        $group->addChild($node);

        $posX = $this->qrcode()->design->logoPositionX - 0.5;

        $posY = 1 - $this->qrcode()->design->logoPositionY - 0.5;

        Log::debug(sprintf('posX = %s posY = %s', $posX, $posY));

        $x = $posX * $this->s->width($this->svg());

        $y = $posY * $this->s->height($this->svg());

        $group->setAttribute(
            'transform',
            sprintf('translate(%s, %s)', $x, $y)
        );

        return $group;
    }

    protected function addImageNode(Imagick $imagick)
    {
        $imageNode = $this->s->embedImagickImage($imagick);

        $imageNode->setAttribute('id', $this::ID_LOGO);

        $this->doc()->addChild($imageNode);

        return $imageNode;
    }

    protected function logoNode()
    {
        return $this->doc()->getElementById($this::ID_LOGO);
    }

    public function sortOrder()
    {
        return 200;
    }
}
