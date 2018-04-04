var nxtMedia = nxtMedia || {};
nxtMedia.htmlstring = '';
nxtMedia.trigger ='';
nxtMedia.overlapping = false;
nxtMedia.callback = function()
{
    var scrollheight = document.body.scrollHeight;
    if( (( scrollheight / screen.height) < 2) || window.innerWidth > 600)
    {
        console.log("screen not long enough or not correct device")
    }
    else
    {
        console.log("screen long enough");
    
        nxtMedia.createAdElements();
        nxtMedia.trigger = document.querySelector('#inter-outer');

        // set the width and height
        // inter-outer, intscdiv, nxtiframe
        document.querySelector('#inter-outer').style.height = screen.height + 'px';
        document.querySelector('#inter-outer').style.right = document.querySelector('#inter-outer').getBoundingClientRect().left + 'px';
        if(nxtMedia.overlapping !== false){
            document.querySelector('#inter-outer').style.top = nxtMedia.overlapping + 'px';
        }

		
        document.querySelector('#intscdiv').style.height = screen.height + 'px';
        document.querySelector('#intscdiv').style.right = window.getComputedStyle(document.body, null).getPropertyValue('padding-left');
        document.querySelector('#intscdiv').style.right = window.getComputedStyle(document.body, null).getPropertyValue('margin-left');
        //document.querySelector('#intscdiv').style.clip = 'rect(0px '+screen.width+'px '+(screen.height + 10)+'px 0px)'
        document.querySelector('#nxtads').style.height = '500px';


        document.querySelector('#inter-outer').style.width = screen.width + 'px';
        document.querySelector('#intscdiv').style.width = screen.width + 'px';
        document.querySelector('#nxtads').style.width = screen.width + 'px';

        document.addEventListener("scroll", nxtMedia.loadAd)
        document.addEventListener("touchmove", nxtMedia.loadAd);
    }
};

nxtMedia.loadAd = function(){
   if(nxtMedia.showJustBeforeView(nxtMedia.trigger) < 5)
   {
     nxtMedia.injectHTMLAgain()

     document.removeEventListener("scroll", nxtMedia.loadAd);
     document.removeEventListener("touchmove", nxtMedia.loadAd);      
   }
}

nxtMedia.createAdElements = function(){

    var outer = document.createElement("div");
	outer.id='inter-outer'; outer.style.width='100%';
    
	
	var inner = document.createElement("div");
    var smx = document.createElement("div");

    outer.id = "inter-outer"; outer.className = "interscroller-wrapper"; outer.style.height = "490px";
    inner.id = "intscdiv"; inner.className = "interscroller-bg-wrapper";
    smx.id = "nxtads"; smx.className = "interscroller-bg";


    inner.appendChild(smx);
    outer.appendChild(inner);
	
	

    var position = (1000);
    var all = document.getElementsByTagName("*");
    var elMiddle = false;
    for(var y in all){
		if(all[y].offsetTop > position && elMiddle === false && all[y].tagName != "A"){
            elMiddle = all[y];
        }
        if(typeof all[y].style != 'undefined' && (all[y].style.position == 'fixed' && all[y].offsetTop == 0)){
            nxtMedia.overlapping = all[y];
        }
    }
    elMiddle.parentNode.insertBefore(outer, elMiddle);
}

nxtMedia.injectHTMLAgain = function(){

	var insert = document.querySelector("#inter-outer");
	insert.innerHTML = '<div style="position: relative; min-width: 0px; min-height: 452px; height: 667px;"> <div style="overflow: hidden; display: block; position: absolute; width: 100%; height: 100%;"><h4 style="text-align:center;position:absolute;top:0;">Advertisment</h4><h4 style="text-align:center;position:absolute;bottom:0;">Scroll to Continue</h4><div style="position: absolute; width: 100%; height: 100%; clip: rect(auto auto auto auto); z-index: 3; margin-left: auto;"> <div style="position: fixed; width: 720px; height: 100%; margin: 0px; padding: 0px; top: 0px; left: initial; transform: translateZ(0px);"> <div style="position: relative; width: 720px; height: 667px; margin: -300px auto 0px 20px; overflow: hidden; padding: 0px; box-sizing: content-box !important; transform: scale(1); top: 50%; left: 0px; transform-origin: left top 0px;"><iframe id="adunit" style="border:0;width:320px;height:490px;" scrolling="no"></iframe></div></div></div></div>';

	
	var check = setInterval(function(){
		if(typeof document.querySelector("#adunit") !== 'undefined'){
			var a = document.querySelector("#adunit").contentWindow.document;
			var cw = document.querySelector("#adunit").contentWindow;
			var s = a.createElement("script"); s.src="https://soma-assets.smaato.net/js/smaatoAdTag.js";
			a.querySelector("head").appendChild(s); 
			a.body.innerHTML = '<div id="smt-130304454" style="padding: 0px"></div>';
			var check2 = setInterval(function(){
				if(typeof a.querySelector("#sm-130304454") !== 'undefined'){

				
					cw.callBackForSmaato = function (status){
						if(status=="SUCCESS")
						{
							console.log("callBack is being called with status : " + status);
						}
						else if (status=="ERROR")
						{
							console.log("callBack is being called with status : " + status);
						}
					}; 

					
					cw.SomaJS.loadAd({adDivId : "smt-130304454",publisherId: 0,adSpaceId: 0,format: "all",formatstrict: true,dimension: "full_320x480",secure:true,width: 320,height: 480,sync: false,},cw.callBackForSmaato);
					clearInterval(check2);
										
				
				}
			},100);
			clearInterval(check);
		}
	},100);		
};


nxtMedia.showJustBeforeView = function(obj){
    var doc = document.documentElement;
    var browserHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    var viewportTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

    // ad position
    var adTop = obj.offsetTop;
    var adBottom = obj.offsetTop + obj.offsetHeight;
    // viewport / window position
    var windowTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
    var windowBottom = viewportTop + browserHeight;

    return (adTop - windowBottom);
}

nxtMedia.isInFullViewport = function(obj) 
{
        var elementTop = obj.offsetTop;
        var elementBottom = elementTop + obj.offsetHeight;

        var doc = document.documentElement;
        var viewportTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

        var browserHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        var viewportBottom = viewportTop + browserHeight;

        return (elementBottom + obj.offsetHeight) > viewportTop && (elementTop + obj.offsetHeight) < viewportBottom;
};

