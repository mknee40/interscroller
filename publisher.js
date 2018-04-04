
(function(){
  insertCSS("https://engine.nxt.media/inter.css");
  insertScript("https://engine.nxt.media/nxtmedia.js");
  //insertScript("https://soma-assets.smaato.net/js/smaatoAdTag.js")
  //insertScript("https://engine.nxt.media/smtads.js");
  //insertScript("",d);
  function insertScript(url){
    var s = document.createElement('script'); s.type = 'text/javascript'; s.async = false;s.src = url + '?r=' + Math.random(); 
    //var i = document.getElementsByTagName('script')[0]; i.parentNode.insertBefore(s,i);
    document.body.appendChild(s);
  }
  function insertCSS(url){
    var c = document.createElement("link"); c.rel = "stylesheet"; c.href=url;
    var h = document.getElementsByTagName("head")[0]; h.parentNode.insertBefore(c,h);
  }
  var getQueryParam = function(param) {
    var found = false;
    window.location.search.substr(1).split("&").forEach(function(item) {
      if (param ==  item.split("=")[0]) {
        found = decodeURIComponent(item.split("=")[1]);
      }
    });
    return found;
  };
  _nxt.demo = getQueryParam("nxt_demo");
})();
