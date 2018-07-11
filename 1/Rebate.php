<?php 
require_once dirname(dirname(__FILE__)).'/config/config.php';

class Rebate
{
    protected $id;
    protected $codeRebate;
    protected $value;
    protected $type; // 1 - %; 0 - PLN
    protected $bestBeforeDate;
    protected $status;
    protected $product;
    protected $addDate;

    public function getId() { return $this->id; }
    public function getCodeRebate() { return $this->codeRebate; }
    public function getValue() { return $this->value; }
    public function getType() { return $this->type; }
    public function getBestBeforeDate() { return $this->bestBeforeDate; }
    public function getStatus() { return $this->status; }
    public function getProduct() { return $this->product; }
    public function getAddDate() { return $this->addDate; }
    
    function __construct($id = null)
    {
        if ($id != null) {
            $this->id = $id;
            $this->loadRebete();
        }
    }

    public function loadRebete()
    {
        $database = dbCon();
        $sql = $database -> select("obrazomat_rebate", '*', ['id' => $this->id]);
        foreach ($sql as $v) {
            $this->id = $v['id'];
            $this->codeRebate = $v['code_rebate'];
            $this->value = $v['value'];
            $this->type = $v['type'];
            $this->bestBeforeDate = $v['best-before_date'];
            $this->status = $v['status'];
            $this->product = $v['product'];
            $this->addDate = $v['add_date'];
        }
    }
}