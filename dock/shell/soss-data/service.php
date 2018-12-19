<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class SearchServices {

    public function postq($req){
        $sall=$req->Body(true);
        $f=new stdClass();
        foreach($sall as $s){
            $result= CacheData::getObjects(md5($s->search),$s->storename);
            if(!isset($result)){
                $result=null;
                if($s->search!=""){
                    $result = SOSSData::Query ($s->storename,urlencode($s->search));
                }else{
                    $result = SOSSData::Query ($s->storename,null);
                }
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        if($s->search==""){
                            $s->search="all";
                        }
                        CacheData::setObjects(md5($s->search),$s->storename,$result->result);
                    }
                }else{
                    $f->{$s->storename}=null;
                }
            }else{
                $f->{$s->storename}= $result;
            }
            
        }
        return $f;
    }

    public function getattribute($req){
        
    }
}

?>