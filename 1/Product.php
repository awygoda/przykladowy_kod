<?php 
require_once dirname(dirname(__FILE__)).'/config/config.php';

class Product
{
    protected $id;
    protected $name;
    protected $overprintOne = 0;
    protected $overprintTwo = 0;
    protected $supplementGallery = 0;
    protected $price = 0;
    protected $addDate;
    protected $shadow;
    protected $name_group_frame;
    protected $color_effect;
    protected $manipulation;
    protected $type; // 1 - wybór formatu z listy; 2 - według zdefiniowanych wartości; 3 - zdefiniowane przez użytkownika
    // array
    protected $bedHeader = [];
    protected $colorEffect = [];
    protected $edition = [];
    protected $finishHeader = [];
    protected $photoLocalization = [];
    protected $frameHeader = [];
    protected $printSecurity = [];
    protected $typeDetails = [];
    protected $typePriceDetails = [];
    protected $typeHeight;
    protected $typeWidth;
    
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getOverprintOne() { return $this->overprintOne; }
    public function getOverprintTwo() { return $this->overprintTwo; }
    public function getSupplementGallery() { return $this->supplementGallery; }
    public function getPrice() { return $this->price; }
    public function getAddDate() { return $this->addDate; }
    public function getType() { return $this->type; }
    public function getShadow() { return $this->shadow; }
    public function getNameGroupFrame() { return $this->name_group_frame; }
    public function getColorEffectStatus() { return $this->color_effect; }
    public function getManipulation() { return $this->manipulation; }

    public function getBedHeader() { return $this->bedHeader; }
    public function getColorEffect() { return $this->colorEffect; }
    public function getEdition() { return $this->edition; }
    public function getFinishHeader() { return $this->finishHeader; }
    public function getPrintSecurity() { return $this->printSecurity; }
    public function getFrameHeader() { return $this->frameHeader; }
    public function getPhotoLocalization() { return $this->photoLocalization; }
    public function getTypeDetails() { return $this->typeDetails; }
    public function getTypePriceDetails() { return $this->typePriceDetails; }
    public function getTypeHeight() { return $this->typeHeight; }
    public function getTypeWidth() { return $this->typeWidth; }
    
    function __construct($id = null)
    {
        if ($id != null) {
            $this->id = $id;
            $this->loadProduct();
        }
    }

    public function loadProduct()
    {
        $database = dbCon();
        $sql = $database -> select("obrazomat_product", '*', ['id' => $this->id]);
        foreach ($sql as $v) {
            $this->id = $v['id'];
            $this->name = $v['name'];
            $this->overprintOne = $v['overprint_one'];
            $this->overprintTwo = $v['overprint_two'];
            $this->supplementGallery = $v['supplement_gallery'];
            $this->price = $v['price'];
            $this->type = $v['type'];
            $this->addDate = $v['add_date'];
            $this->shadow = $v['shadow'];
            $this->name_group_frame = $v['name_group_frame'];
            $this->color_effect = $v['color_effect'];
            $this->manipulation = $v['manipulation'];
            $this->loadProductPhotoLocalization();
            $this->loadProductBedHeader();
            $this->loadProductFinishHeader();
            $this->loadProductPrintSecurity();
            $this->loadProductFrameHeader();
            $this->loadProductColorEffect();
            if ($this->type == 1) {
                // format
                $this->loadTypeFormat();
            } elseif ($this->type == 2) {
                // width height
                $this->loadTypeWidth();
                $this->loadTypeHeight();
                $this->loadPriceDetails();
            } elseif ($this->type == 3) {
                //user
                $this->loadTypeUser();
                $this->loadPriceDetails();
            }
        }
    }

    private function loadProductBedHeader()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_bed_header','*',['product' => $this->id]);
        $this->bedHeader = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "name" => $v["name"],
                "supplement" => $v["supplement"],
            ];
            array_push($this->bedHeader, $array);
        }
    }

    private function loadProductColorEffect()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_color_effect','*',['product' => $this->id]);
        $this->colorEffect = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
            ];
            $array["effects"] = [
                "vintage" => $v["vintage"],
                "noise" => $v["noise"],
                "tiltshift" => $v["tiltshift"],
                "lomo" => $v["lomo"],
                "clarity" => $v["clarity"],
                "sinCity" => $v["sinCity"],
                "sunrise" => $v["sunrise"],
                "crossProcess" => $v["crossProcess"],
                "orangePeel" => $v["orangePeel"],
                "love" => $v["love"],
                "grungy" => $v["grungy"],
                "jarques" => $v["jarques"],
                "pinhole" => $v["pinhole"],
                "oldBoot" => $v["oldBoot"],
                "glowingSun" => $v["glowingSun"],
                "hazyDays" => $v["hazyDays"],
                "herMajesty" => $v["herMajesty"],
                "nostalgia" => $v["nostalgia"],
                "hemingway" => $v["hemingway"],
                "concentrate" => $v["concentrate"],
                "black-white" => $v["black-white"],
                "sepia" => $v["sepia"]];
            array_push($this->colorEffect, $array);
        }
    }

    private function loadProductPrintSecurity()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_print_security','*',['product' => $this->id]);
        $this->printSecurity = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "name" => $v["name"],
                "supplement" => $v["supplement"]
            ];
            array_push($this->printSecurity, $array);
        }
    }

    private function loadProductFinishHeader()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_finish_header','*',['product' => $this->id]);
        $this->finishHeader = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "biale_boki" => $v["biale_boki"],
                "odbicie_lustrzane" => $v["odbicie_lustrzane"],
                "zadrukowane_boki" => $v["zadrukowane_boki"]
            ];
            array_push($this->finishHeader, $array);
        }
    }

    private function loadProductPhotoLocalization()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_photo_localization','*',['product' => $this->id]);
        $this->photoLocalization = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "disc" => $v["disc"],
                "galeria" => $v["galeria"],
                "instagram" => $v["instagram"],
                "fotolia" => $v["fotolia"],
            ];
            array_push($this->photoLocalization, $array);
        }
    }

    public function loadAllProduct()
    {
        $database = dbCon();
        $sql = $database->select('obrazomat_product', 'id');
        $result = [];
        foreach ($sql as $v) {
            $product = new Product($v);
            $result[] = $product;
        }
        return $result;
    }

    public function loadTypeFormat()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_type_format','*',['product' => $this->id]);
        $this->typeDetails = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "format" => $v["format"],
                "price" => $v['price'],
            ];
            array_push($this->typeDetails, $array);
        }
    }

    public function loadTypeWidth()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_type_width','*',['product' => $this->id]);
        $this->typeWidth = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "width" => $v["width"],
            ];
            array_push($this->typeWidth, $array);
        }
    }

    public function loadTypeHeight()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_type_height','*',['product' => $this->id]);
        $this->typeHeight = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "height" => $v["height"],
            ];
            array_push($this->typeHeight, $array);
        }
    }

    public function loadTypeUser()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_type_user','*',['product' => $this->id]);
        $this->typeDetails = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "max_width" => $v["max_width"],
                "max_height" => $v['max_height'],
                "min_width" => $v["min_width"],
                "min_height" => $v['min_height'],
            ];
            array_push($this->typeDetails, $array);
        }
    }

    public function loadPriceDetails()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_price_threshold','*',['product' => $this->id]);
        $this->typePriceDetails = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "from_m2" => $v["from_m2"],
                "to_m2" => $v['to_m2'],
                "price" => $v["price"],
            ];
            array_push($this->typePriceDetails, $array);
        }
    }

    public function loadProductFrameHeader()
    {
        $database = dbCon();
        $sql = $database ->select('obrazomat_product_frame_header','*',['product' => $this->id]);
        $this->frameHeader = [];
        foreach ($sql as $v) {
            $array = [
                "id" => $v["id"],
                "product" => $v["product"],
                "name" => $v["name"],
                "supplement" => $v["supplement"],
                "path" => $v['path'],
                "path_frame" => $v['path_frame']
            ];
            array_push($this->frameHeader, $array);
        }
    }
}