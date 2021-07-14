const imageExt = ["ase","art","bmp","blp","cd5","cit","cpt","cr2","cut","dds","dib","djvu","egt","exif","gif","gpl","grf","icns","ico","iff","jng","jpeg","jpg","jfif","jp2","jps","lbm","max","miff","mng","msp","nitf","ota","pbm","pc1","pc2","pc3","pcf","pcx","pdn","pgm","PI1","PI2","PI3","pict","pct","pnm","pns","ppm","psb","psd","pdd","psp","px","pxm","pxr","qfx","raw","rle","sct","sgi","rgb","int","bw","tga","tiff","tif","vtf","xbm","xcf","xpm","3dv","amf","ai","awg","cgm","cdr","cmx","dxf","e2d","egt","eps","fs","gbr","odg","svg","stl","vrml","x3d","sxd","v2d","vnd","wmf","emf","art","xar","png","webp","jxr","hdp","wdp","cur","ecw","iff","lbm","liff","nrrd","pam","pcx","pgf","sgi","rgb","rgba","bw","int","inta","sid","ras","sun","tga"];
const zipExt = ["7z","aar","ace","arj","apk","arc","ark","br","bz","bz2","cab","chm","deb","dmg","ear","egg","epub","gz","jar","lha","lrz","lz","lz4","lzh","lzma","lzo","lzop","mar","par2","pea","pet","pkg","rar","rpm","rz","s7z","shar","sit","sitx","tbz","tbz2","tgz","tlz","txz","tzo","war","whl","xpi","xz","z","zip","zipx","zoo","zpaq","zst"];
const videoExt = ["3g2","3gp","aaf","asf","avchd","avi","drc","flv","m2v","m4v","mkv","mng","mov","mp4","mpe","mpeg","mpg","mpv","mxf","nsv","ogv","qt","rm","rmvb","roq","svi","vob","webm","wmv","yuv"];
const audioExt =  ["wav","bwf","raw","aiff","flac","m4a","pac","tta","wv","ast","aac","mp2","mp3","amr","s3m","act","au","dct","dss","gsm","m4p","mmf","mpc","ogg","oga","opus","ra","sln","vox"];
const powerpointExt = ["ppt","pot","pps","pptx","pptm","potx","potm","ppam","ppsx","ppsm","sldx","sldm"];
const excelExt = ["xls","xlt","xlm","xlsx","xlsm","xltx","xltm","xlsb","xla","xlam","xll","xlw"];
const wordExt = ["doc","dot","wbk","docx","docm","dotx","dotm","docb"];
const webExt = ["html","css","js","php"];
const glossaExt = ["psg","glo"];
const pythonExt = "py";
const pdfExt = "pdf";

function iconFromExtension(file){
    let ext = file.split('.').pop().toLowerCase();

    if(ext == pdfExt) return 'pdf';
    if(ext == pythonExt) return 'python';
    if(glossaExt.indexOf(ext) > -1) return 'glossa';
    if(webExt.indexOf(ext) > -1) return 'web';
    if(wordExt.indexOf(ext) > -1) return 'word';
    if(excelExt.indexOf(ext) > -1) return 'excel';
    if(powerpointExt.indexOf(ext) > -1) return 'powerpoint';
    if(audioExt.indexOf(ext) > -1) return 'audio';
    if(videoExt.indexOf(ext) > -1) return 'video';
    if(zipExt.indexOf(ext) > -1) return 'zip';
    if(imageExt.indexOf(ext) > -1) return 'image';
    return 'file';
}