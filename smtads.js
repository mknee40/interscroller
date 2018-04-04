window.addEventListener("load",function()
{
	console.log("loading nxt..smt...");
	var checkExist = setInterval(function() {
	   if (document.querySelector("#smt-130304454")!== null) 
	   {
			console.log("Element Exists on page!");
			SomaJS.loadAd({
			adDivId : "smt-130304454",
			//publisherId: 1100033462,
			//adSpaceId: 130304454,
			publisherId: 0,
			adSpaceId: 0,
			format: "all",formatstrict: true,dimension: "full_320x480",width: 320,height: 480,
			sync: false,age: 25,gender: "m",},callBackForSmaato);
			clearInterval(checkExist);
	   }
	}, 100);    

    function callBackForSmaato(status)
	{
        if(status == "SUCCESS"){
            console.log("success");
        }else if(status == "ERROR"){
            console.log("Error: " + status);
            if(nxtMedia.bizrate !== false){
                document.querySelector('#smt-130304454').innerHTML = nxtMedia.bizrate.request.htmlString;
            }
			else{
				document.querySelector("#inter-outer").style.display="none";
			}
        }else{
            console.log("Why the fuck doesnt it work");
        }
    };
});