<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class ArticalService{
    public function postSaveArtical($req,$res){
        
        $Artical=$req->Body(true);
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        $summery =new stdClass();
        $summery->summery=$Artical->summery;
        $summery->title=$Artical->title;
        $summery->title=$Artical->title;
        //if(isset())
        $summery->imgname=isset($Artical->imgname)? $Artical->imgname : '';
        //echo "im in"
        if(!isset($Artical->id)){
            $result=SOSSData::Insert ("d_cms_artical_v1", $Artical,$tenantId = null);
            //return $result;
            //var_dump($result);
            if($result->success){
                $Artical->id = $result->result->generatedId;
                $summery->id=$result->result->generatedId;
                SOSSData::Insert ("d_cms_artical_summery_v1", $summery,$tenantId = null);
                //return $Artical;
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }else{
            $result=SOSSData::Update ("d_cms_artical_v1", $Artical,$tenantId = null);
            $summery->id=$Artical->id;
            SOSSData::Update ("d_cms_artical_summery_v1", $summery,$tenantId = null);
        }
        CacheData::clearObjects("d_cms_artical_v1");
        CacheData::clearObjects("d_cms_artical_summery_v1");
        foreach($Artical->Images as $key=>$value){
            $Artical->Images[$key]->articalid=$Artical->id;
            if($Artical->Images[$key]->id==0){
                $result2=SOSSData::Insert ("d_cms_artical_imagev1", $Artical->Images[$key],$tenantId = null);
                if($result2->success){
                    $Artical->Images[$key]->id = $result2->result->generatedId;
                }

            }else{
                $result2=SOSSData::Update ("d_cms_artical_imagev1", $Artical->Images[$key],$tenantId = null);
            }
            
            //var_dump($invoice->InvoiceItems[$key]->invoiceNo);
        }
        CacheData::clearObjects("d_cms_artical_imagev1");
        return $Artical;
        
    }

    function getArtical($req){
        //echo "imain";
        $data =null;
        if(isset($_GET["q"])){
            $result= CacheData::getObjects_fullcache(md5("id:".$_GET["q"]),"d_cms_artical_summery_v1");
            if(!isset($result)){
                $result = SOSSData::Query ("d_cms_artical_summery_v1",urlencode("id:".$_GET["q"]));
                if($result->success){
                    //$f->{$s->storename}=$result->result;
                    if(isset($result->result[0])){
                        $data= $result->result[0];
                        CacheData::setObjects(md5("id:".$_GET["q"]),"d_cms_artical_summery_v1",$result->result);
                    }
                }
            }else{
                $data= $result[0];
            }
            //$result = SOSSData::Query ("d_cms_artical_v1",urlencode("id:".$_GET["q"]));
            //var_dump($result);
            //echo "imain";
            if(isset($data)){
                
                
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8" />
                    <meta name="description" content="'.urldecode($data->summery).'">
                    <meta name="tags" content="'.urldecode($data->tags).'">
                    <meta name="og:title" content="'.urldecode($data->title).'">
                    <meta name="og:description" content="'.urldecode($data->summery).'">
                    <meta name="og:tags"  content="'.urldecode($data->tags).'">
                    <meta name="og:image"  content="http://'.$_SERVER["HTTP_HOST"].'/components/dock/soss-uploader/service/get/d_cms_artical/'.$_GET["q"]."-".$data->imgname.'">
                    <title>'.urldecode($data->title).'</title>
                    
                </head>
                <body>
                    loading.....
                    <script type="text/javascript">
                        setTimeout(function(){ window.location = "/#/app/davvag-cms-generalapps/a?id='.$_GET["q"].'"; }, 1000);
                        
                    </script>    
                </body>
                </html>';
                exit();      

            }
        }
    }

}
?>