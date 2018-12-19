WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance,uploaderInstance;
    var routeData;
    var newfiles;
    
    var bindData = {
        product:{},
        uoms:[],
        content:"",
        submitErrors:[],
        input:"# hed",
        p_image:[],
        files:null

    };

    var vueData =   {
        
        methods: {
            submit: submit,
            gotoUom: gotoUom,
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            },removeImage: removeImage,
            onFileChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                createImage(files);
            },
            filecontent:function(file){
                var reader = new FileReader();
                reader.onload = function (e) {
                //console.log(e);
                //console.log(files[i]);
                    return e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        computed: {
          compiledMarkdown: function () {
            return marked(bindData.input);
          }
        },
        data : bindData,
        onReady : function(s){
            scope = s;
            handler = exports.getComponent("cms-gapp-handler");
            pInstance = exports.getShellComponent("soss-routes");
            validatorInstance = exports.getShellComponent ("soss-validator");
            uploaderInstance = exports.getShellComponent("soss-uploader");
            routeData = pInstance.getInputData();
            $('#grnDatePicker').datepicker().on('changeDate', function(ev){
                bindData.product.createdate = $('#grnDatePicker').val(); 
            });
            loadValidator();
            if (routeData)
                loadCategory(scope);
           
        }
    }


    function removeImage(e) {
        bindData.p_image = [];
    }


    function createImage(files) {
        //console.log(JSON.stringify(files));
        //if(!newfiles){
        newfiles=[];
        //}
        for (var i = 0; i < files.length; i++) {
            newfiles.push(files[i]);
            getImage(i,files[i]);
            //console.log();
        }
        

        console.log(JSON.stringify(bindData.p_image));
    }

    function getImage(index,file){
        var reader = new FileReader();
            reader.onload = function (e) {
                //console.log(e);
                //console.log(newfiles);
                newfiles[index].scr=e.target.result;
                
                bindData.p_image.push({id:0,name:newfiles[index].name,scr:e.target.result,file:file});
                console.log(newfiles);
            };
        reader.readAsDataURL(file);
    }

    var imagecount=0;
    var completed=0;    
    function uploadFile(productId, cb){
            if(!newfiles){
                cb();
                return;
            }
            imagecount=newfiles.length;
            for (var i = 0; i < newfiles.length; i++) {
                //newfiles.push(newFile[i]);
                //getImage(i,files[i]);
                //console.log();
                //imagecount++;
                console.log(i);

                        uploaderInstance.services.uploadFile(newfiles[i], "d_cms_artical", productId+"-"+newfiles[i].name )
                        .then(function(result2){
                            $.notify("Profile Image Has been uploaded", "info");
                            completed++;
                            if(imagecount==completed){
                                cb();
                            }
                            //cb();
                        })
                        .error(function(){
                            completed++;
                            $.notify("Profile Image Has not been uploaded", "error");
                            //cb();
                            if(imagecount==completed){
                                cb();
                            }
                        });
                    
                    
                    
                  
            }
            //cb();
        
    }

    function loadCategory(scope){
        if (routeData.id){
            handler.transformers.getArticalById(routeData.id)
            .then(function(result){
                if (result.result.length !=0){
                    bindData.product = result.result[0];
                    
                    bindData.product.content=bindData.product.content.split("~^").join("'");
                    bindData.product.content=bindData.product.content.split('~*').join('"');
                    /*bindData.product.title=unescape(bindData.product.title);
                    bindData.product.content=unescape(bindData.product.content);
                    bindData.product.summery=unescape(bindData.product.summery);
                    bindData.product.tags=unescape(bindData.product.tags);*/
                    bindData.p_image =[];// 'components/dock/soss-uploader/service/get/d_cms_artical/'+bindData.product.id;
                }
            })
            .error(function(){
        
            });

            handler.transformers.getImagesByArticalId(routeData.id).then(function(result){
                if (result.result.length !=0){
                    bindData.p_image =  result.result;
                    for (var i = 0; i < bindData.p_image.length; i++) {
                        bindData.p_image[i].scr='components/dock/soss-uploader/service/get/d_cms_artical/'+bindData.product.id+'-'+bindData.p_image[i].name;
                    }
                    //bindData.p_image =[];// 'components/dock/soss-uploader/service/get/d_cms_artical/'+bindData.product.id;
                }
            })
            .error(function(){
                
            });
        }
    }

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("product.title",true, "You should enter a name");
        validator.map ("product.summery",true, "You should enter a summery");
        validator.map ("product.content",true, "You should enter a content");
        validator.map ("product.tags",true, "You should enter a tags");
    }
    

    function submit(){
        $('#send').prop('disabled', true);
        bindData.submitErrors = validator.validate(); 
        if (!bindData.submitErrors){
            bindData.product.content=bindData.product.content.split("'").join("~^");
            bindData.product.content=bindData.product.content.split('"').join("~*");
            bindData.product.Images=[];
            for (var i = 0; i < bindData.p_image.length; i++) {
                bindData.product.Images.push({id:bindData.p_image[i].id,name:bindData.p_image[i].name,
                    caption:bindData.p_image[i].caption,default_img:bindData.p_image[i].default_img});
            }
            //if(!routeData.id)
                //bindData.product.id=0
            var promiseObj = handler.services.SaveArtical(bindData.product);
            //else promiseObj = handler.transformers.insertArtical (bindData.product);
            

            promiseObj
            .then(function(result){
                //uploadFile(promiseObj.)
                
                uploadFile(result.result.id, function(){
                    gotoUom();
                });
                
            })
            .error(function(){
                $('#send').prop('disabled', false);
            });
        }else{
            $('#send').prop('disabled', false);
        }
    }

    function gotoUom(){
        handler1 = exports.getShellComponent("soss-routes");
        handler1.appNavigate("../articalall");
    }


    exports.vue = vueData;
    exports.onReady = function(element){
        
    }
});
