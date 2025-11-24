<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGSymbol;

class BookShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'book';

    public function symbolPath()
    {
        return 'm 84.202326,645.4601 c 1.16636,-1.91634 43.263474,-102.09791 93.549144,-222.62576 50.28566,-120.52783 94.93176,-226.0085 99.21355,-234.4015 12.28522,-24.08101 40.73245,-50.10799 62.23443,-56.93959 15.55286,-4.94146 59.92596,-6.66132 262.53104,-10.17548 134.09811,-2.32592 244.71563,-3.32803 245.81676,-2.22692 2.34493,2.34496 -163.50917,478.364 -173.84828,498.96306 -12.35627,24.61797 -16.14268,25.62074 -66.77325,17.6838 C 542.13615,625.5812 442.98655,620.21413 369.27815,622.87369 217.57072,628.34756 113.105,637.03958 89.317594,646.16764 c -4.142305,1.58956 -6.329267,1.28706 -5.115268,-0.70754 z';
    }

    protected function frameNode()
    {
        $symbol = new SVGSymbol();

        $symbol->setAttribute(
            'id',
            'frame-node-' . $this::$shapeId
        );

        $symbol->setAttribute(
            'viewBox',
            $this->symbolViewBox()
        );

        $path = new SVGPath(
            'm 91.39377,580.36636 c 130.9388,3.57812 261.87761,7.15623 392.81641,10.73435 M 91.391677,530.27261 c 130.939453,3.5781 261.878913,7.1562 392.818363,10.7343 M 50.215208,499.13917 c -5.813009,28.69178 -20.269087,56.59575 -14.185263,86.37076 2.842294,26.18777 27.430158,55.26768 55.961642,53.14108 11.388673,1.3105 22.777673,2.62037 34.167003,3.92961 m -7.45658,0 c 136.90365,3.88933 273.80729,7.77866 410.71094,11.66799 47.22331,-133.23763 94.44661,-266.47526 141.66992,-399.71289 M 647.37801,81.565785 c -125.44157,2.18787 -250.90796,3.963216 -376.33386,6.410959 -41.94125,3.177462 -69.18837,40.374576 -81.56019,77.298986 L 50.215208,499.13917 C 70.69304,481.6997 166.20663,478.24907 232.30635,475.24743 514.35141,461.51419 480.9343,519.95646 514.35141,461.51419 558.69361,334.86472 603.03581,208.21525 647.37801,81.565785 Z'
        );

        $symbol->addChild($path);

        $this->doc->addChild($symbol);

        return $this->use(
            $symbol,
            $this->getViewBoxStart(),
            $this->getViewBoxStart(),
            '100%',
            '100%'
        );
    }


    public function symbolTransform()
    {
        return 'scale(0.75)';
    }

    public function symbolViewBox()
    {
        return '0 0 700 700';
    }

    protected function renderStyles()
    {
        return sprintf(
            '.foreground-0 {
            transform: translate(-1%%, -14%%) scale(1.3);
        }
        .foreground-1 {
            mask: url(#%s);
        }',
            $this->maskId()
        );
    }

    protected function frameStrokeWidth()
    {
        return 10;
    }
}
