<?php
require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");
require_once (PLUGIN_PATH . "/auth/auth.php");
class ProductService {
    
    private function saveAttributes($product){
        if(isset($product->attributes)){
            $attributes = $product->attributes;
            //$attributes->itemid=$product->itemid;
            $r=null;
            if(isset($product->attributes->itemid))
                $r=SOSSData::Update ("products_attributes", $attributes,$tenantId = null);
            else{
                $attributes->itemid=$product->itemid;
                $r=SOSSData::Insert ("products_attributes", $attributes,$tenantId = null);
            }
            if($r->success){
                $product->attributes=$attributes;
            }else{
                $product->attributes=null;
            }
            return $product;

        }else{
            return $product;
        }
    }

    public function postSave($req,$res){
        
        $product=$req->Body(true);
        $user= Auth::Autendicate("product","save",$res);
        $summery =new stdClass();
        $summery->summery=$product->caption;
        $summery->title=$product->name;
        
        //if(isset())
        $summery->imgname=isset($product->imgurl)? $product->imgurl : '';
        //echo "im in"
        if(!isset($product->itemid)){
            $result=SOSSData::Insert ("products", $product,$tenantId = null);
            //return $result;
            //var_dump($result);
            if($result->success){
                $product->itemid = $result->result->generatedId;
                $summery->id=$result->result->generatedId;
                $product=$this->saveAttributes($product);
                //$summery->imgname=$result->result->generatedId;
                SOSSData::Insert ("d_all_summery", $summery,$tenantId = null);
                //return $product;
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }else{
            $result=SOSSData::Update ("products", $product,$tenantId = null);
            $summery->id=$product->itemid;
            if($result->success){
                $product=$this->saveAttributes($product);
                SOSSData::Update ("d_all_summery", $summery,$tenantId = null);
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }
        CacheData::clearObjects("products");
        CacheData::clearObjects("d_all_summery");
        CacheData::clearObjects("products_attributes");
        foreach($product->Images as $key=>$value){
            $product->Images[$key]->articalid=$product->itemid;
            if($product->Images[$key]->id==0){
                $result2=SOSSData::Insert ("products_image", $product->Images[$key],$tenantId = null);
                if($result2->success){
                    $product->Images[$key]->id = $result2->result->generatedId;
                }

            }else{
                $result2=SOSSData::Update ("products_image", $product->Images[$key],$tenantId = null);
            }
            
            //var_dump($invoice->InvoiceItems[$key]->invoiceNo);
        }
        CacheData::clearObjects("products_image");
        return $product;
        
    }

    public function getAllProducts($req){
        if (isset($_GET["lat"]) && isset($_GET["lng"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->lat = $_GET["lat"];
            $mainObj->parameters->lng = $_GET["lng"];
            $mainObj->parameters->catid = isset($_GET["catid"]) ?  $_GET["catid"] : "";
            $resultObj = SOSSData::ExecuteRaw("nearproducts", $mainObj);
            for ($i=0;$i<sizeof($resultObj->result);$i++){
                $obj = $resultObj->result[$i];
                $obj->inventory = new stdClass();
                $obj->inventory->productid=1;
                $obj->inventory->locationid=1;
                $obj->inventory->qty=1;
                $obj->inventory->status="";
            }
            header("Content-type: application/json");
            $outObj = new stdClass();
            $outObj->success = true;
            $outObj->result = $resultObj->result;
            echo json_encode($outObj);
            exit();
            return $resultObj->result;
        } else {
            require_once (PLUGIN_PATH . "/transactions/transactions.php");

            $query = isset($_GET["catid"]) ? "catogory:$_GET[catid]" : null;
            $tranObj = TransactionManager::Create();
            $tranObj->Get->__invoke("products", $query, "@OBJ")
                    ->IterateAndJoin->__invoke("@OBJ", "_->_=#->inventory->/inventory->productid=#->itemid");
            $result = $tranObj->Execute();
    
            $objs = $result->processData->object;
    
            return $objs;
        }
    }
}

?>