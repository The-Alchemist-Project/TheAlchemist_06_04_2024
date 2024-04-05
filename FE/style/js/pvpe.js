var files = [
    "jquery",
    "bootstrap",
    "jquery.easing",
    "jquery.mousewheel",
    "jquery.jscrollpane",
    "map",
    "metro",
    "playlist",
    "../fancybox/jquery.fancybox",
    "../fancybox/helpers/jquery.fancybox-buttons",
    "../fancybox/helpers/jquery.fancybox-thumbs"
    //"rotate",
   // "grid"
];
for (var i = 0; i < files.length; i++) {
    document.write("<script src='"+base_url+"/style/js/" + files[i] + ".min.js'></script>");
}
