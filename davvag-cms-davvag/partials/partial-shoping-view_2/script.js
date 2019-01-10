WEBDOCK.component().register(function(exports){
    var page=0;
    var size=40;
    //var q
    //document.body.addEventListener('scroll', loadproducts);
    var bindData={
        products:[],
        product:{caption:""},
        q:""
    };
    var vueData =  {
        methods:{
        selectStore: function(p){
            bindData.product=p;
            $('#modalImagePopup').modal('show');
        },selectStoreClose: function(){
            //bindData.product=p;
            $('#modalImagePopup').modal('toggle');
        }, 
        onSearch () {
            if(bindData.q!=""){
                page=0;
                bindData.products=[];
                loadproducts();
            }
        },
        OnkeyEnter: function(e){
            if (e.keyCode === 13) {
                if(bindData.q!=""){
                    page=0;
                    bindData.products=[];
                    loadproducts();
                }
            }
        }
        },
        data :bindData
        ,
        onReady: function(s){
           
            loadproducts();
            
        },
        filters:{
            markeddown: function (value) {
                if (!value) return ''
                value = value.toString()
                return marked(unescape(value));
              },
              dateformate:function(v){
                  if(!v){
                      return ""
                  }else{
                    return moment(v, "MM-DD-YYYY hh:mm:ss").format('MMMM Do YYYY');
                  }
              }
        }
    } 

    function loadproducts(){
        var menuhandler  = exports.getComponent("productsvr");
            //var query=[{storename:"products",search:""}];
            menuhandler.services.allProducts({page:page, size:size+"&page="+page+"&q=", q:bindData.q})
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                r.result.forEach(element => {
                                    bindData.products.push(element);
                                });
                                
                                page=page+40;
                            }
                        })
                        .error(function(error){
                            bindData.products=[];
                            console.log(error.responseJSON);
            });
    }

    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
