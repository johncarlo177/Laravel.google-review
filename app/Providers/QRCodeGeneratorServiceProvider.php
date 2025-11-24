<?php

namespace App\Providers;

use App\Repositories\QRCodeGenerator;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\CouponAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\FourCornersTextBottomAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\FourCornersTextTopAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\HealthcareAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\PINCodeProtectedAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\QRCodeDetailsAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\QRCodeWithLogoAndSlugAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\RectFrameTextBottomAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\RectFrameTextTopAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\ReviewCollectorAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\SimpleTextBottomAdvancedShape;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\SimpleTextTopAdvancedShape;
use App\Support\QRCodeProcessors\AiSvgBuilder;
use App\Support\QRCodeProcessors\AlignmentProcessor;
use App\Support\QRCodeProcessors\BackgroundProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\CircleShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\CloudShapeProcessor;

use App\Support\QRCodeProcessors\DarkMaskProcessor;
use App\Support\QRCodeProcessors\DummyDataProcessor;
use App\Support\QRCodeProcessors\FinderDotProcessors\CircleFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\DefaultFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\EyeShapedFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\OctagonFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\RoundedCornersFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\WaterDropFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\WhirlpoolFinderDot;
use App\Support\QRCodeProcessors\FinderDotProcessors\ZigZagFinderDot;
use App\Support\QRCodeProcessors\FinderProcessors\CircleDotsFinder;
use App\Support\QRCodeProcessors\FinderProcessors\CircleFinder;
use App\Support\QRCodeProcessors\FinderProcessors\DefaultFinder;
use App\Support\QRCodeProcessors\FinderProcessors\EyeShapedFinder;
use App\Support\QRCodeProcessors\FinderProcessors\OctagonFinder;
use App\Support\QRCodeProcessors\FinderProcessors\ZigZagFinder;
use App\Support\QRCodeProcessors\FinderProcessors\RoundedCornersFinder;
use App\Support\QRCodeProcessors\FinderProcessors\WaterDropFinder;
use App\Support\QRCodeProcessors\FinderProcessors\WhirlpoolFinder;
use App\Support\QRCodeProcessors\ForegroundImageProcessor;
use App\Support\QRCodeProcessors\GradientProcessor;
use App\Support\QRCodeProcessors\LogoProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\AppleShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BagShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BakeryShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BarnShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\CupShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\GiftShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\HomeShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BookShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BootShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BrainShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BuilderShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BulbShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\BurgerShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\CarShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\CookingShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\DentistShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\ElectricianShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\FoodShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\FurnitureShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\GardeningShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\GolfShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\GymShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\HomeMoverShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\IceCreamShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\JuiceShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\LegalShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\LocksmithShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\MessageShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\MobileShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PainterShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PestShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PetShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PiggyBankShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PizzaShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\PlumberShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\RealtorShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\RealtorSignShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\RestaurantShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\SalonShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\SearchShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\ShawarmaShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\ShieldShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\ShirtShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\ShoppingCartShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\StarShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\SunRiseShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\SunShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\TeddyShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\TicketShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\TravelShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\TrophyShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\TruckShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\UmbrellaShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\VanShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\WatchShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\WaterGlassShapeProcessor;
use App\Support\QRCodeProcessors\ShapeProcessors\WaterShapeProcessor;
use App\Support\QRCodeProcessors\SvgBuilder;

use App\Support\SvgModuleRenderer\DotModule;
use App\Support\SvgModuleRenderer\DynamicModuleRenderer;
use App\Support\SvgModuleRenderer\HorizontalLinesModule;
use App\Support\SvgModuleRenderer\PolygonModule;
use App\Support\SvgModuleRenderer\RoundnessModule;
use App\Support\SvgModuleRenderer\SquareModule;
use App\Support\SvgModuleRenderer\TriangleEndModule;
use App\Support\SvgModuleRenderer\VerticalLinesModule;
use App\Support\SvgOutput;

use Illuminate\Support\ServiceProvider;

class QRCodeGeneratorServiceProvider extends ServiceProvider
{
    protected static $outlinedShapes = [];

    public static function registerOutlinedShape($classString)
    {
        static::$outlinedShapes[] = $classString;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        QRCodeGenerator::processor(SvgBuilder::class);
        QRCodeGenerator::processor(DarkMaskProcessor::class);

        // Finders
        QRCodeGenerator::processor(DefaultFinder::class);
        QRCodeGenerator::processor(RoundedCornersFinder::class);
        QRCodeGenerator::processor(ZigZagFinder::class);
        QRCodeGenerator::processor(OctagonFinder::class);
        QRCodeGenerator::processor(WaterDropFinder::class);
        QRCodeGenerator::processor(EyeShapedFinder::class);
        QRCodeGenerator::processor(WhirlpoolFinder::class);
        QRCodeGenerator::processor(CircleFinder::class);
        QRCodeGenerator::processor(CircleDotsFinder::class);

        // Finder Dots
        QRCodeGenerator::processor(DefaultFinderDot::class);
        QRCodeGenerator::processor(EyeShapedFinderDot::class);
        QRCodeGenerator::processor(OctagonFinderDot::class);
        QRCodeGenerator::processor(RoundedCornersFinderDot::class);
        QRCodeGenerator::processor(WhirlpoolFinderDot::class);
        QRCodeGenerator::processor(WaterDropFinderDot::class);
        QRCodeGenerator::processor(CircleFinderDot::class);
        QRCodeGenerator::processor(ZigZagFinderDot::class);

        // Alignment should be after finders and finder dots.

        QRCodeGenerator::processor(AlignmentProcessor::class);


        // Background
        QRCodeGenerator::processor(BackgroundProcessor::class);

        // Shapes
        QRCodeGenerator::processor(CircleShapeProcessor::class);
        QRCodeGenerator::processor(CloudShapeProcessor::class);
        QRCodeGenerator::processor(ShoppingCartShapeProcessor::class);
        QRCodeGenerator::processor(GiftShapeProcessor::class);
        QRCodeGenerator::processor(CupShapeProcessor::class);
        QRCodeGenerator::processor(ShirtShapeProcessor::class);
        QRCodeGenerator::processor(HomeShapeProcessor::class);
        QRCodeGenerator::processor(BookShapeProcessor::class);
        QRCodeGenerator::processor(MessageShapeProcessor::class);
        QRCodeGenerator::processor(BagShapeProcessor::class);
        QRCodeGenerator::processor(TruckShapeProcessor::class);
        QRCodeGenerator::processor(TrophyShapeProcessor::class);
        QRCodeGenerator::processor(UmbrellaShapeProcessor::class);
        QRCodeGenerator::processor(VanShapeProcessor::class);
        QRCodeGenerator::processor(WatchShapeProcessor::class);
        QRCodeGenerator::processor(WaterShapeProcessor::class);
        QRCodeGenerator::processor(BulbShapeProcessor::class);
        QRCodeGenerator::processor(SunShapeProcessor::class);
        QRCodeGenerator::processor(CarShapeProcessor::class);
        QRCodeGenerator::processor(PetShapeProcessor::class);
        QRCodeGenerator::processor(GymShapeProcessor::class);
        QRCodeGenerator::processor(SalonShapeProcessor::class);
        QRCodeGenerator::processor(FoodShapeProcessor::class);
        QRCodeGenerator::processor(IceCreamShapeProcessor::class);
        QRCodeGenerator::processor(SearchShapeProcessor::class);
        QRCodeGenerator::processor(BurgerShapeProcessor::class);
        QRCodeGenerator::processor(AppleShapeProcessor::class);
        QRCodeGenerator::processor(BarnShapeProcessor::class);
        QRCodeGenerator::processor(SunRiseShapeProcessor::class);
        QRCodeGenerator::processor(StarShapeProcessor::class);
        QRCodeGenerator::processor(RealtorShapeProcessor::class);
        QRCodeGenerator::processor(LegalShapeProcessor::class);
        QRCodeGenerator::processor(JuiceShapeProcessor::class);
        QRCodeGenerator::processor(WaterGlassShapeProcessor::class);
        QRCodeGenerator::processor(ElectricianShapeProcessor::class);
        QRCodeGenerator::processor(PlumberShapeProcessor::class);
        QRCodeGenerator::processor(BuilderShapeProcessor::class);
        QRCodeGenerator::processor(HomeMoverShapeProcessor::class);
        QRCodeGenerator::processor(CookingShapeProcessor::class);
        QRCodeGenerator::processor(GardeningShapeProcessor::class);
        QRCodeGenerator::processor(FurnitureShapeProcessor::class);
        QRCodeGenerator::processor(MobileShapeProcessor::class);
        QRCodeGenerator::processor(RestaurantShapeProcessor::class);
        QRCodeGenerator::processor(TravelShapeProcessor::class);
        QRCodeGenerator::processor(DentistShapeProcessor::class);
        QRCodeGenerator::processor(GolfShapeProcessor::class);
        QRCodeGenerator::processor(PizzaShapeProcessor::class);
        QRCodeGenerator::processor(LocksmithShapeProcessor::class);
        QRCodeGenerator::processor(BakeryShapeProcessor::class);
        QRCodeGenerator::processor(PainterShapeProcessor::class);
        QRCodeGenerator::processor(PestShapeProcessor::class);
        QRCodeGenerator::processor(TeddyShapeProcessor::class);
        QRCodeGenerator::processor(BootShapeProcessor::class);
        QRCodeGenerator::processor(ShieldShapeProcessor::class);
        QRCodeGenerator::processor(ShawarmaShapeProcessor::class);
        QRCodeGenerator::processor(TicketShapeProcessor::class);
        QRCodeGenerator::processor(PiggyBankShapeProcessor::class);
        QRCodeGenerator::processor(RealtorSignShapeProcessor::class);
        QRCodeGenerator::processor(BrainShapeProcessor::class);

        foreach ($this::$outlinedShapes as $classString) {
            QRCodeGenerator::processor($classString);
        }

        // Other functionalities
        QRCodeGenerator::processor(DummyDataProcessor::class);
        QRCodeGenerator::processor(GradientProcessor::class);
        QRCodeGenerator::processor(ForegroundImageProcessor::class);

        // Ai Generator to Override existing svg if needed

        QRCodeGenerator::processor(AiSvgBuilder::class);
        QRCodeGenerator::processor(LogoProcessor::class);


        // Advanced shapes
        QRCodeGenerator::processor(RectFrameTextTopAdvancedShape::class);
        QRCodeGenerator::processor(RectFrameTextBottomAdvancedShape::class);
        QRCodeGenerator::processor(SimpleTextBottomAdvancedShape::class);
        QRCodeGenerator::processor(SimpleTextTopAdvancedShape::class);
        QRCodeGenerator::processor(FourCornersTextTopAdvancedShape::class);
        QRCodeGenerator::processor(FourCornersTextBottomAdvancedShape::class);
        QRCodeGenerator::processor(CouponAdvancedShape::class);
        QRCodeGenerator::processor(ReviewCollectorAdvancedShape::class);
        QRCodeGenerator::processor(HealthcareAdvancedShape::class);
        QRCodeGenerator::processor(PINCodeProtectedAdvancedShape::class);
        QRCodeGenerator::processor(QRCodeDetailsAdvancedShape::class);
        QRCodeGenerator::processor(QRCodeWithLogoAndSlugAdvancedShape::class);


        // Modules
        SvgOutput::moduleRenderer(SquareModule::class);
        SvgOutput::moduleRenderer(DotModule::class);
        SvgOutput::moduleRenderer(PolygonModule::class);
        SvgOutput::moduleRenderer(RoundnessModule::class);
        SvgOutput::moduleRenderer(VerticalLinesModule::class);
        SvgOutput::moduleRenderer(HorizontalLinesModule::class);
        SvgOutput::moduleRenderer(DynamicModuleRenderer::class);
        SvgOutput::moduleRenderer(TriangleEndModule::class);
    }
}
