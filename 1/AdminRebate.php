<?php
include_once dirname(dirname(dirname(__FILE__))).'/classes/Rebate.php';

class AdminRebate extends Rebate
{
    public function saveRebate($data)
    {
        $database = dbCon();
        $this->codeRebate = htmlspecialchars(strip_tags(trim($data['code-rebate'])));
        $this->bestBeforeDate = htmlspecialchars(strip_tags(trim($data['best-before-date'])));
        $this->value = htmlspecialchars(strip_tags(trim($data['value'])));
        if (isset($data['product'])) {
            $product = $data['product'];
        } else {
            throw new Exception('Wybierz kategorię produktów.');
        }
        if ($data["allowance-code-type"] == 'percent') {
            $this->type = 1; // %
        } else {
            $this->type = 0; // PLN
        }
        if (empty($this->codeRebate) || $this->codeRebate == "") {
            throw new Exception('Wpisz nazwę kodu rabatowego.');
        }
        if (empty($this->bestBeforeDate) || $this->bestBeforeDate == "") {
            throw new Exception('Wybierz termin ważności.');
        }
        if (empty($this->value) || $this->value == "") {
            throw new Exception('Wpisz wartość kodu.');
        }
        foreach ($product as $v) {
            if ($database-> has('obrazomat_rebate',["AND"=>['product' => $v, 'code_rebate' => $this->codeRebate]])) {
                $k = $database->get('obrazomat_product' , 'name', ['id' => $v]);
                throw new Exception('Kod o takiej nazwie dla produktu ' .$k .' istnieje.');
            }
        }
        $this->value = str_replace(",", ".", $this->value );
        if ($this->bestBeforeDate < date("Y-m-d")) {
            throw new Exception('Data nie może być wcześniejsza niż dzisiejsza.');
        }
        foreach ($product as $v) {
            if ($v == 0) {
                $sql = $database->select('obrazomat_product', 'id');
                foreach ($sql as $k) {
                    if(!$database->insert('obrazomat_rebate',[
                        'status' => 0,
                        'type' => $this->type,
                        'code_rebate' =>$this->codeRebate,
                        'best-before_date' => $this->bestBeforeDate,
                        'value' => $this->value,
                        'product' => $k,
                        'add_date' => date('Y-m-d H:i:s')
                    ])) {
                        throw new Exception('Wystąpił problem z zapisaniem danych.');
                    }
                }
                break;
            } else {
                if(!$database->insert('obrazomat_rebate',[
                    'status' => 0,
                    'type' => $this->type,
                    'code_rebate' =>$this->codeRebate,
                    'best-before_date' => $this->bestBeforeDate,
                    'value' => $this->value,
                    'product' => $v,
                    'add_date' => date('Y-m-d H:i:s')
                ])) {
                    throw new Exception('Wystąpił problem z zapisaniem danych.');
                }
            }
        }
    }

    public function loadAllRebate()
    {
        $database = dbCon();
        $sql = $database -> select("obrazomat_rebate", 'id',['ORDER' => ['add_date'=>'DESC']]);
        $rebates = [];
        if (!empty($sql)) {
            foreach ($sql as $v) {
                $rebate = new AdminRebate($v);
                $rebates[] = $rebate;
            }
        } 
        return $rebates;
    }

    public function changeStatusRebate()
    {
        $database = dbCon();
        if ($this->status) {
            if (!$database->update("obrazomat_rebate",['status' => 0],['id' => $this->id])) {
                throw new Exception('Wystąpił problem z zapisaniem danych.');
            }
        } else {
            if(!$database->update("obrazomat_rebate",['status' => 1],['id' => $this->id])) {
                throw new Exception('Wystąpił problem z zapisaniem danych.');
            }
        }
    }
}