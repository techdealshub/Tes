<?php

$html = file_get_contents("latest-reviews.html");

$dom = new DOMDocument();

libxml_use_internal_errors(true);
$dom->loadHTML($html);
// // ==========================================
// استخراج CSS الموجود في الصفحة
// Style + External CSS
// ==========================================

$pageCSS = "";
$pageLinks = "";

$pageJS = "";


// CSS داخل <style>
$styleTags = $dom->getElementsByTagName("style");

foreach($styleTags as $style){

    $pageCSS .= $style->textContent . "\n";

}


// CSS خارجي <link rel="stylesheet">
$linkTags = $dom->getElementsByTagName("link");

foreach($linkTags as $link){

    $rel = strtolower($link->getAttribute("rel"));

    if($rel == "stylesheet"){

        $href = $link->getAttribute("href");

        if($href){

            $pageLinks .= '<link rel="stylesheet" href="' .
                htmlspecialchars($href, ENT_QUOTES, 'UTF-8') .
                '">' . "\n";

        }

    }

}
// ==========================================
// استخراج JavaScript
// Inline JS + External JS
// ==========================================

$scriptTags = $dom->getElementsByTagName("script");

foreach($scriptTags as $script){

    // ======================================
    // JavaScript خارجي
    // ======================================

    if($script->hasAttribute("src")){

        $src = $script->getAttribute("src");

        if($src){

            $pageJS .=
                '<script src="' .
                htmlspecialchars(
                    $src,
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                '"></script>' .
                "\n";

        }

    }

    // ======================================
    // JavaScript داخلي
    // ======================================

    else{

        $js = $script->textContent;

        if(trim($js) !== ""){

            $pageJS .=
                '<script>' .
                $js .
                '</script>' .
                "\n";

        }

    }

}
libxml_clear_errors();


// ==========================================
// تحويل المستوى إلى A B C ... AA AB ...
// ==========================================

function getLevelName($level){

    $name = "";

    while($level >= 0){

        $name = chr(($level % 26) + 65) . $name;
        $level = floor($level / 26) - 1;

    }

    return $name;
}


// ==========================================
// قائمة المستويات
// ==========================================

echo "<ul class='levels'>";

for($i = 0; $i < 10; $i++){

    $levelName = getLevelName($i);

    echo "
    <li onclick=\"showLevel('$levelName')\">
        $levelName
    </li>
    ";

}

echo "</ul>";


// ==========================================
// استخراج جميع الوسوم
// ==========================================

$tags = [];

$allElements = $dom->getElementsByTagName('*');

foreach($allElements as $element){

    $tagName = strtolower($element->nodeName);

    if(!in_array($tagName, $tags)){

        $tags[] = $tagName;

    }

}

sort($tags);


// ==========================================
// قائمة الوسوم
// ==========================================

echo "<ul class='tags-list'>";

foreach($tags as $tag){

    $safeTag = htmlspecialchars($tag, ENT_QUOTES);

    echo "
    <li onclick=\"showTag('$safeTag')\">
        &lt;$tag&gt;
    </li>
    ";

}

echo "</ul>";


// ==========================================
// رسم الشجرة
// ==========================================

function drawTree($node, $level = 0){

    if($node->nodeType != XML_ELEMENT_NODE){
        return;
    }


    $nameLevel = getLevelName($level);


    // ==========================================
    // الكود الكامل للعنصر
    // ==========================================

    $fullHTML = $node->ownerDocument->saveHTML($node);

$fullHTML = htmlspecialchars(
    $fullHTML,
    ENT_QUOTES,
    'UTF-8'
);


    echo "
    <div 
        class='node level-$nameLevel'
        data-html='$fullHTML'
    >";


    // ==========================================
    // الزر
    // ==========================================

    echo "
    <button class='toggle' data-tag='" .
    htmlspecialchars($node->nodeName, ENT_QUOTES) .
    "'>
    ";


    // ==========================================
    // منطقة الكتابة 70%
    // ==========================================

    echo "<span class='toggle-text'>";

    echo "&lt;" . htmlspecialchars($node->nodeName) . "&gt;";


    // ==========================================
    // الخصائص
    // ==========================================

    if($node->hasAttributes()){

        foreach($node->attributes as $attr){

            $attrName  = $attr->name;
            $attrValue = htmlspecialchars($attr->value);

            if($attrName == "class"){

                echo " ." . $attrValue;

            }

            else if($attrName == "id"){

                echo " #" . $attrValue;

            }

            else{

                echo " " . $attrName . "=" . $attrValue;

            }

        }

    }

    echo "</span>";


    // ==========================================
    // مستوى العنصر
    // ==========================================

    echo "
    <span class='level-name'>
        $nameLevel
    </span>
    ";


    // ==========================================
    // زر Edit
    // ==========================================

    echo "
    <span class='edit-btn'
        onclick=\"event.stopPropagation(); editNode(this)\">
        Edit
    </span>
    ";


    echo "</button>";


    // ==========================================
    // التفاصيل
    // ==========================================

    echo "<div class='details'>";

    echo "<b>Tag:</b> " .
         htmlspecialchars($node->nodeName) .
         "<br>";


    if($node->hasAttributes()){

        foreach($node->attributes as $attr){

            echo "
            <b>" . htmlspecialchars($attr->name) . "</b>
            :
            " . htmlspecialchars($attr->value) . "
            <br>
            ";

        }

    }

    echo "</div>";


    // ==========================================
    // الأبناء
    // ==========================================

    $children = [];

    foreach($node->childNodes as $child){

        if($child instanceof DOMElement){

            $children[] = $child;

        }

    }


    if(count($children)){

        echo "<div class='children'>";

        foreach($children as $child){

            drawTree($child, $level + 1);

        }

        echo "</div>";

    }


    echo "</div>";

}


// ==========================================
// بدء رسم الشجرة
// ==========================================

drawTree($dom->documentElement);

?>
<script>

const pageCSS = <?= json_encode($pageCSS) ?>;

const pageLinks = <?= json_encode($pageLinks) ?>;

const pageJS = <?= json_encode($pageJS) ?>;

</script>

<style>

*{
    box-sizing:border-box;
}

body{
    background:#050816;
    color:white;
    font-family:Arial;
    margin:0;
    padding:10px;
}


/* ==========================================
   NODE
========================================== */

.node{
    margin-left:0;
    width:100%;
}


/* ==========================================
   BUTTON
========================================== */

.toggle{

    display:flex;

    align-items:center;

    position:relative;

    background:#111b35;
    color:white;

    border:0;
    border-radius:15px;

    padding:15px;
    margin:5px 0;

    font-size:18px;

    cursor:pointer;

    text-align:left;

    width:100%;

    min-width:0;

}


/* ==========================================
   الكتابة فقط 70%
========================================== */

.toggle-text{

    display:block;

    width:70%;

    min-width:0;

    overflow-wrap:anywhere;
    word-break:break-word;

    white-space:normal;

    line-height:1.5;

}


/* ==========================================
   مستوى العنصر
========================================== */

.level-name{

    margin-left:auto;

    flex-shrink:0;

    background:orange;

    color:#000;

    padding:2px 7px;

    border-radius:10px;

    font-weight:700;

    font-size:14px;

}


/* ==========================================
   EDIT
========================================== */

.edit-btn{

    flex-shrink:0;

    background:#3b82f6;

    color:white;

    padding:4px 8px;

    margin-left:8px;

    border-radius:6px;

    font-size:12px;

    font-weight:bold;

    cursor:pointer;

}

.edit-btn:hover{

    background:orange;
    color:#000;

}


/* ==========================================
   CHILDREN
========================================== */

.children{

    margin-left:0;

    width:100%;

}


/* ==========================================
   DETAILS
========================================== */

.details{

    display:none;

    background:#1e293b;

    padding:10px;

    margin-left:10px;

    margin-bottom:5px;

    border-radius:10px;

    width:calc(100% - 10px);

    overflow-wrap:anywhere;

    word-break:break-word;

}


/* ==========================================
   LEVELS
========================================== */

.levels{

    display:flex;

    gap:10px;

    list-style:none;

    overflow-x:auto;

    padding:5px;

    margin:5px 0;

}

.levels li{

    flex-shrink:0;

    background:#111b35;

    color:white;

    padding:10px 15px;

    border-radius:10px;

    cursor:pointer;

}

.levels li:hover{

    background:orange;
    color:#000;

}

.levels::-webkit-scrollbar{

    display:none;

}


/* ==========================================
   TAGS
========================================== */

.tags-list{

    display:flex;

    gap:10px;

    list-style:none;

    overflow-x:auto;

    padding:5px;

    margin:5px 0;

}

.tags-list li{

    flex-shrink:0;

    background:#111b35;

    color:white;

    padding:10px 15px;

    border-radius:10px;

    cursor:pointer;

    white-space:nowrap;

}

.tags-list li:hover{

    background:orange;
    color:#000;

}

.tags-list::-webkit-scrollbar{

    display:none;

}
/* ==========================================
   LIVE EDITOR
========================================== */

.live-editor{
    position:fixed;
    inset:0;
    z-index:99999;

    background:rgba(0,0,0,.75);

    display:flex;
    align-items:center;
    justify-content:center;

    padding:15px;
}


/* ==========================================
   LIVE EDITOR BOX
========================================== */

.live-editor-box{
    width:100%;
    max-width:1100px;
    height:90vh;

    background:#0d1729;

    border-radius:15px;

    overflow:hidden;

    display:flex;
    flex-direction:column;
}


/* ==========================================
   HEADER
========================================== */

.live-editor-header{
    height:55px;
    min-height:55px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 15px;

    background:#111b35;
    color:white;
}


/* ==========================================
   CLOSE
========================================== */

.live-close{
    border:0;

    background:#ef4444;
    color:white;

    width:35px;
    height:35px;

    border-radius:8px;

    font-size:25px;

    cursor:pointer;
}


/* ==========================================
   BODY
========================================== */

.live-editor-body{
    flex:1;

    display:flex;

    min-height:0;
    min-width:0;

    overflow:hidden;
}


/* ==========================================
   CODE PANEL
========================================== */

.code-panel{
    width:50%;
    height:100%;

    display:flex;
    flex-direction:column;

    min-width:0;
    min-height:0;

    overflow:hidden;
}


/* ==========================================
   PANEL TITLE
========================================== */

.panel-title{
    flex-shrink:0;

    background:#1e293b;
    color:white;

    padding:10px 15px;

    font-weight:bold;
}


/* ==========================================
   TEXTAREA
========================================== */

.code-input{
    display:block;

    flex:1;

    width:100%;
    height:100%;

    min-width:0;
    min-height:0;

    resize:none;

    border:0;
    outline:0;

    margin:0;
    padding:15px;

    background:#020617;
    color:#e2e8f0;

    font-family:monospace;
    font-size:14px;
    line-height:1.6;

    overflow:auto;
}


/* ==========================================
   PREVIEW PANEL
========================================== */

.preview-panel{
    position:relative;

    width:50%;
    height:100%;

    display:flex;
    flex-direction:column;

    min-width:0;
    min-height:0;

    border-left:1px solid #334155;

    overflow:hidden;
}


/* ==========================================
   LIVE PREVIEW
========================================== */

.live-preview{
    display:block;

    flex:1;

    width:100%;
    height:100%;

    min-width:0;
    min-height:0;

    border:0;

    margin:0;
    padding:0;

    background:white;
}


/* ==========================================
   HTML TREE
========================================== */

.tree-panel{
    position:absolute;

    top:55px;
    right:8px;

    width:200px;
    height:250px;

    z-index:99999;
    
    display:flex;
    flex-direction:column;

    min-width:0;
    min-height:0;

    margin:0;
    padding:0;

    background:#050816;

    border:1px solid #334155;

    border-radius:8px;

    overflow:hidden;

    box-shadow:0 8px 25px rgba(0,0,0,.6);
}


/* ==========================================
   TREE TITLE
========================================== */

.tree-panel .panel-title{
    flex-shrink:0;

    width:100%;

    padding:6px 8px;

    margin:0;

    font-size:12px;

    line-height:1.3;

    background:#111b35;
}


/* ==========================================
   TREE CONTENT
========================================== */

.html-tree{
    flex:1;

    width:100%;
    height:100%;

    min-width:0;
    min-height:0;

    overflow-y:auto;
    overflow-x:hidden;

    padding:5px;
    margin:0;
}


/* ==========================================
   TREE NODE
========================================== */

.tree-node{
    width:100%;

    margin:0;
    padding:0;
}


/* ==========================================
   TREE TAG
========================================== */

.tree-tag{
    display:block;

    width:100%;

    min-width:0;

    background:#111b35;
    color:white;

    border:0;

    border-radius:5px;

    padding:4px 6px;

    margin:2px 0;

    text-align:left;

    cursor:pointer;

    font-family:monospace;

    font-size:11px;

    line-height:1.3;

    overflow-wrap:anywhere;
    word-break:break-word;
}


/* ==========================================
   TREE TAG NAME
========================================== */

.tree-tag-name{
    color:#60a5fa;
}


/* ==========================================
   TREE COUNT
========================================== */

.tree-count{
    float:right;

    background:#334155;
    color:white;

    padding:1px 4px;

    border-radius:4px;

    font-size:9px;

    line-height:1.2;
}


/* ==========================================
   TREE CHILDREN
========================================== */

.tree-children{
    width:100%;

    margin:0;
    margin-left:8px;

    padding:0 0 0 5px;

    border-left:1px solid #334155;
}


/* ==========================================
   COLLAPSED
========================================== */

.tree-children.collapsed{
    display:none;
}


/* ==========================================
   MOBILE
========================================== */

@media(max-width:700px){

    .live-editor{
        padding:0;
    }


    .live-editor-box{
        width:100%;
        height:100%;

        max-width:none;

        border-radius:0;
    }


    /* ======================================
       BODY
    ====================================== */

    .live-editor-body{
        display:flex;

        flex-direction:column;

        width:100%;
        height:calc(100% - 55px);

        min-height:0;
    }


    /* ======================================
       CODE PANEL
    ====================================== */

    .code-panel{
        width:100%;

        height:50%;
        min-height:0;

        flex:0 0 50%;

        border-bottom:1px solid #334155;
    }


    /* ======================================
       TEXTAREA
    ====================================== */

    .code-input{
        display:block;

        width:100%;
        height:100%;

        min-height:0;

        flex:1;

        padding:10px;

        font-size:13px;

        overflow:auto;
    }
.code-wrapper{
    position:relative;

    flex:1;

    min-width:0;
    min-height:0;

    display:flex;
    flex-direction:column;

    overflow:hidden;
}


/* ==========================================
   CSS LIVE EDITOR
========================================== */

.selected-class{

    display:none;

    flex-shrink:0;

    width:100%;

    height:180px;

    min-height:100px;

    resize:vertical;

    border:0;

    outline:0;

    border-bottom:1px solid #334155;

    padding:10px 15px;

    margin:0;

    background:#020617;

    color:#facc15;

    font-family:monospace;

    font-size:14px;

    line-height:1.6;

    white-space:pre;

    overflow:auto;

    tab-size:4;

}


/* ==========================================
   HTML TEXTAREA
========================================== */

.code-wrapper .code-input{

    flex:1;

    width:100%;

    min-width:0;

    min-height:0;

    border:0;

    outline:0;

    resize:none;

    padding:15px;

    margin:0;

    background:#020617;

    color:#e2e8f0;

    font-family:monospace;

    font-size:14px;

    line-height:1.6;

    overflow:auto;

}

.code-wrapper .code-input{

    flex:1;

    width:100%;

    min-width:0;
    min-height:0;

    border:0;

    outline:0;

    resize:none;

}

    /* ======================================
       PREVIEW
    ====================================== */

    .preview-panel{
        width:100%;

        height:50%;
        min-height:0;

        flex:0 0 50%;

        border-left:0;

        border-top:0;

        position:relative;
    }


    /* ======================================
       LIVE PREVIEW
    ====================================== */

    .live-preview{
        width:100%;
        height:100%;

        min-height:0;
    }


    /* ======================================
       TREE فوق Preview
    ====================================== */

    .tree-panel{

        position:absolute;
       
        top:0;
        left:0;
        right:0;

        width:100%;

        height:250px;

        margin:0;
        padding:0;

        border-radius:0;

        z-index:99999;
        
    }


    /* ======================================
       TREE TITLE
    ====================================== */

    .tree-panel .panel-title{

        width:100%;

        height:30px;

        padding:6px 10px;

        margin:0;

        font-size:11px;
    }


    /* ======================================
       TREE CONTENT
    ====================================== */

    .html-tree{

        width:100%;

        height:calc(100% - 30px);

        flex:1;

        margin:0;

        padding:5px;

        overflow-y:auto;
        overflow-x:hidden;
    }


    /* ======================================
       TREE TAG
    ====================================== */

    .tree-tag{

        width:100%;

        padding:3px 5px;

        margin:1px 0;

        font-size:10px;

        line-height:1.25;
    }


    .tree-tag-name{
        font-size:13px;
    }


    .tree-count{

        font-size:8px;

        padding:1px 3px;
    }

}
.tree-panel .panel-title{
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.tree-close{
    border:0;
    background:#ef4444;
    color:white;
    width:25px;
    height:25px;
    border-radius:5px;
    cursor:pointer;
    font-size:18px;
    line-height:20px;
}
/* ==========================================
   LIVE TREE
========================================== */

.tree-panel{

    position:absolute;

    top:40px;
    left:8px;

    width:calc(100% - 16px);
    height:230px;

    z-index:99999;

    background:#050816;

    border:1px solid #334155;
    border-radius:8px;

    overflow:hidden;

    box-shadow:0 5px 20px rgba(0,0,0,.6);

}


/* ==========================================
   TREE HEADER
========================================== */

.tree-panel .panel-title{

    height:32px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:4px 7px;

    font-size:12px;

    background:#111b35;

}


/* ==========================================
   CLOSE
========================================== */

.tree-close{

    border:0;

    background:#ef4444;

    color:white;

    width:24px;
    height:24px;

    border-radius:5px;

    font-size:17px;

    line-height:20px;

    cursor:pointer;

}


/* ==========================================
   A B C D E F
========================================== */

.live-levels{

    display:flex;

    gap:4px;

    padding:4px;

    overflow-x:auto;

}


.live-levels button{

    flex-shrink:0;

    border:0;

    background:#111b35;

    color:white;

    padding:4px 8px;

    border-radius:5px;

    font-size:11px;

    cursor:pointer;

}


/* ==========================================
   TAGS
========================================== */

.live-tags{

    display:flex;

    gap:4px;

    padding:4px;

    overflow-x:auto;

    border-bottom:1px solid #1e293b;

}


.live-tags button{

    flex-shrink:0;

    border:0;

    background:#111b35;

    color:white;

    padding:4px 7px;

    border-radius:5px;

    font-size:10px;

    cursor:pointer;

}


.live-tags button.active{

    background:orange;

    color:#000;

}


/* ==========================================
   TREE
========================================== */

.html-tree{

    height:calc(100% - 78px);

    overflow-y:auto;

    overflow-x:hidden;

    padding:4px;

}


/* ==========================================
   TREE ITEM
========================================== */

.tree-tag{

    display:flex;

    align-items:center;

    width:100%;

    min-width:0;

    border:0;

    background:#111b35;

    color:white;

    border-radius:5px;

    padding:4px 6px;

    margin:1px 0;

    text-align:left;

    font-family:monospace;

    font-size:10px;

    line-height:1.2;

    cursor:pointer;

}


.tree-tag-name{

    flex:1;

    min-width:0;

    overflow-wrap:anywhere;

}


.tree-count{

    flex-shrink:0;

    margin-left:4px;

    background:#334155;

    padding:1px 4px;

    border-radius:4px;

    font-size:8px;

}


/* ==========================================
   HIGHLIGHT
========================================== */

.tree-tag.highlight{

    background:orange;

    color:#000;

}


@media(max-width:700px){

    .tree-panel{

        top:38px;

        left:5px;

        width:calc(100% - 10px);

        height:210px;

    }

    .live-levels button{

        padding:3px 7px;

        font-size:10px;

    }

    .live-tags button{

        padding:3px 6px;

        font-size:9px;

    }

    .tree-tag{

        font-size:9px;

        padding:3px 5px;

    }

}
.tree-open{
    display:none;
    padding:8px 14px;
    background:#111b35;
    color:white;
    border:0;
    border-radius:8px;
    cursor:pointer;
}
</style>

<script>

// =====================================================
// فتح وإغلاق العناصر
// =====================================================

document.querySelectorAll(".toggle").forEach(btn => {

    btn.addEventListener("click", function(){

        const info = this.nextElementSibling;

        if(info && info.classList.contains("details")){

    info.style.display =
    info.style.display === "block"
    ? "none"
    : "block";

}

        const child =
            this.parentElement.querySelector(
                ":scope > .children"
            );

        if(child){

            child.style.display =
                child.style.display === "block"
                ? "none"
                : "block";

            child.querySelectorAll(
                ":scope > .node"
            ).forEach(n => {

                n.style.display = "block";

            });

        }

    });

});


// =====================================================
// عرض مستوى معين
// =====================================================

// =====================================================
// عرض مستوى معين
// =====================================================

function showLevel(level){

    document.querySelectorAll(".node").forEach(node => {

        node.style.display = "none";

    });


    document.querySelectorAll(".details").forEach(details => {

        details.style.display = "none";

    });


    document.querySelectorAll(".children").forEach(children => {

        children.style.display = "none";

    });


    document.querySelectorAll(
        ".level-" + level
    ).forEach(node => {

        node.style.display = "block";


        let parent =
            node.parentElement.closest(".node");

        while(parent){

            parent.style.display = "block";

            parent =
                parent.parentElement.closest(".node");

        }


        let current =
            node.parentElement;

        while(current){

            if(
                current.classList &&
                current.classList.contains("children")
            ){

                current.style.display =
                    "block";

            }

            current =
                current.parentElement;

        }

    });

}


// =====================================================
// عرض Tag معين
// =====================================================

function showTag(tag){

    document.querySelectorAll(".toggle").forEach(button => {

        button.style.background = "#111b35";
        button.style.color = "white";

    });


    document.querySelectorAll(".node").forEach(node => {

        node.style.display = "none";

    });


    document.querySelectorAll(".details").forEach(details => {

        details.style.display = "none";

    });


    document.querySelectorAll(".children").forEach(children => {

        children.style.display = "none";

    });


    document.querySelectorAll(".node").forEach(node => {

        const button =
            node.querySelector(":scope > .toggle");

        if(!button){
            return;
        }


        if(button.dataset.tag === tag){

            node.style.display = "block";

            button.style.background = "orange";
            button.style.color = "#000";


            let parent =
                node.parentElement.closest(".node");

            while(parent){

                parent.style.display = "block";

                parent =
                    parent.parentElement.closest(".node");

            }


            let current =
                node.parentElement;

            while(current){

                if(
                    current.classList &&
                    current.classList.contains("children")
                ){

                    current.style.display = "block";

                }

                current =
                    current.parentElement;

            }

        }

    });

}


// =====================================================
// EDIT NODE + LIVE EDITOR
// =====================================================

function editNode(button){

    const node =
        button.closest(".node");

    if(!node){
        return;
    }


    const html =
        node.dataset.html;

    if(!html){

        console.error(
            "Live Editor: data-html غير موجود"
        );

        return;

    }


    // =================================================
    // إنشاء Modal
    // =================================================

    const modal =
        document.createElement("div");

    modal.className =
        "live-editor";


    // =================================================
    // محتوى Live Editor
    // =================================================

    modal.innerHTML = `

        <div class="live-editor-box">

            <div class="live-editor-header">

                <b>Live Editor</b>

                <button
                    type="button"
                    class="live-close">
                    ×
                </button>

            </div>


            <div class="live-editor-body">


                <!-- =================================
                     HTML TREE
                ================================== -->

               <div class="tree-panel">

    <div class="panel-title">

        <span>
            HTML Tree
        </span>

        <button
            type="button"
            class="tree-close">
            ×
        </button>

    </div>

    <div class="live-levels"></div>

    <div class="live-tags"></div>

    <div class="html-tree"></div>

</div>


<button
    type="button"
    class="tree-open">
    HTML Tree
</button>
                <!-- =================================
                     HTML
                ================================== -->

                <div class="code-panel">

                    <div class="panel-title">
                        HTML
                    </div>

                    <div class="code-wrapper">

                        <textarea
                            class="selected-class"
                            spellcheck="false"
                            placeholder=".class{
    property:value;
}"
                        ></textarea>

                        <textarea
                            class="code-input"
                            spellcheck="false"
                        ></textarea>

                    </div>

                </div>


                <!-- =================================
                     LIVE PREVIEW
                ================================== -->

                <div class="preview-panel">

                    <div class="panel-title">
                        Live Preview
                    </div>

                    <iframe
                        class="live-preview"
                        title="Live Preview">
                    </iframe>

                </div>


            </div>

        </div>

    `;


    // =================================================
    // إضافة Modal إلى الصفحة
    // =================================================

    document.body.appendChild(modal);


    // =================================================
    // عداد data-live-name
    // =================================================

    let liveElementCounter = 0;

    
    // =================================================
    // إنشاء / استرجاع data-live-name
    // =================================================

    function getLiveName(element){

    if(!element){
        return "";
    }

    let liveName =
        element.getAttribute(
            "data-live-name"
        );

    if(liveName){
        return liveName;
    }

    liveElementCounter++;

    liveName =
        "data-name" +
        liveElementCounter;

    element.setAttribute(
        "data-live-name",
        liveName
    );

    return liveName;
}


// =================================================
// الحصول على العناصر
// =================================================

const textarea =
    modal.querySelector(".code-input");

const selectedClass =
    modal.querySelector(".selected-class");

const iframe =
    modal.querySelector(".live-preview");

const treeContainer =
    modal.querySelector(".html-tree");

const liveTags =
    modal.querySelector(".live-tags");

const closeButton =
    modal.querySelector(".live-close");

const treePanel =
    modal.querySelector(".tree-panel");

const treeClose =
    modal.querySelector(".tree-close");

const treeOpen =
    modal.querySelector(".tree-open");



// =================================================
// فحص العناصر الأساسية
// =================================================

// =================================================
// فحص العناصر الأساسية
// =================================================

if(!textarea){

    console.error(
        "Live Editor: .code-input غير موجود"
    );

    modal.remove();

    return;
}


if(!iframe){

    console.error(
        "Live Editor: .live-preview غير موجود"
    );

    modal.remove();

    return;
}


if(!treeContainer){

    console.error(
        "Live Editor: .html-tree غير موجود"
    );

    modal.remove();

    return;
}


// =================================================
// وضع HTML داخل textarea
// =================================================

textarea.value =
    html;


// =================================================
// إغلاق Live Editor
// =================================================

if(closeButton){

    closeButton.addEventListener(
        "click",
        function(){

            modal.remove();

        }
    );

}


// =================================================
// إغلاق HTML TREE
// =================================================

if(
    treeClose &&
    treePanel
){

    treeClose.addEventListener(
        "click",
        function(event){

            event.preventDefault();
            event.stopPropagation();

            treePanel.style.display =
                "none";

            if(treeOpen){

                treeOpen.style.display =
                    "block";

            }

        }
    );

}


// =================================================
// فتح HTML TREE
// =================================================

if(
    treeOpen &&
    treePanel
){

    treeOpen.addEventListener(
        "click",
        function(event){

            event.preventDefault();
            event.stopPropagation();

            treePanel.style.display =
                "block";

            treeOpen.style.display =
                "none";

        }
    );

}


// =================================================
// الحالة الابتدائية لـ HTML TREE
// =================================================

if(treePanel){

    treePanel.style.display =
        "block";

}


if(treeOpen){

    treeOpen.style.display =
        "none";

}
// =================================================
// تحديد Tag
// =================================================
// =================================================
// فتح HTML Tree
// =================================================



    // =================================================
    // تحديد Tag
    // =================================================

    function highlightTag(tag){

        treeContainer
            .querySelectorAll(".tree-tag")
            .forEach(item => {

                item.classList.remove(
                    "highlight"
                );

            });


        treeContainer
            .querySelectorAll(".tree-tag")
            .forEach(item => {

                if(
                    item.dataset.tag ===
                    tag.toLowerCase()
                ){

                    item.classList.add(
                        "highlight"
                    );

                }

            });


        liveTags
            .querySelectorAll("button")
            .forEach(button => {

                button.classList.remove(
                    "active"
                );

            });


        const selectedButton =
            liveTags.querySelector(
                'button[data-tag="' +
                tag +
                '"]'
            );


        if(selectedButton){

            selectedButton.classList.add(
                "active"
            );

        }

    }
        // =====================================================
    // الحصول على Selector خاص بالعنصر
    // =====================================================

    function getEditorSelector(element){

        const liveName =
            getLiveName(element);


        // =================================================
        // ID
        // =================================================

        if(element.id){

            return (
                "#" +
                CSS.escape(element.id) +
                '[data-live-name="' +
                liveName +
                '"]'
            );

        }


        // =================================================
        // CLASS
        // =================================================

        if(
            element.classList &&
            element.classList.length
        ){

            const validClasses =
                Array.from(
                    element.classList
                )
                .filter(className =>
                    /^[a-zA-Z_-][a-zA-Z0-9_-]*$/.test(
                        className
                    )
                );


            if(validClasses.length){

                return (
                    "." +
                    validClasses
                        .map(className =>
                            CSS.escape(className)
                        )
                        .join(".") +

                    '[data-live-name="' +
                    liveName +
                    '"]'
                );

            }

        }


        // =================================================
        // لا يوجد ID ولا CLASS
        // =================================================

        return (
            element.tagName.toLowerCase() +
            '[data-live-name="' +
            liveName +
            '"]'
        );

    }


    // =====================================================
    // تحديد عنصر داخل Live Preview
    // =====================================================

    function highlightElement(element){

        if(!element){
            return;
        }


        const iframeDoc =
            iframe.contentDocument ||
            iframe.contentWindow.document;


        if(!iframeDoc){
            return;
        }


        // =================================================
        // إزالة التحديد السابق
        // =================================================

        iframeDoc
            .querySelectorAll(
                "[data-live-highlight]"
            )
            .forEach(el => {

                el.style.outline = "";

                el.style.backgroundColor = "";

                el.removeAttribute(
                    "data-live-highlight"
                );

            });


        // =================================================
        // إنشاء Selector فريد
        // =================================================

        function getUniqueSelector(el){

            const parts = [];


            while(
                el &&
                el.nodeType ===
                    Node.ELEMENT_NODE &&
                el !== iframeDoc.body
            ){

                let selector =
                    el.tagName.toLowerCase();


                // =================================================
                // ID
                // =================================================

                if(el.id){

                    selector +=
                        "#" +
                        CSS.escape(
                            el.id
                        );


                    parts.unshift(
                        selector
                    );

                    break;

                }


                // =================================================
                // CLASS
                // =================================================

                if(
                    el.classList &&
                    el.classList.length
                ){

                    const classes =
                        Array.from(
                            el.classList
                        )
                        .filter(
                            className =>
                                /^[a-zA-Z_-][a-zA-Z0-9_-]*$/
                                    .test(
                                        className
                                    )
                        );


                    if(classes.length){

                        selector +=
                            "." +
                            classes
                                .map(
                                    className =>
                                        CSS.escape(
                                            className
                                        )
                                )
                                .join(".");

                    }

                }


                // =================================================
                // تحديد nth-of-type
                // =================================================

                const parent =
                    el.parentElement;


                if(parent){

                    const sameTags =
                        Array.from(
                            parent.children
                        )
                        .filter(
                            child =>
                                child.tagName ===
                                el.tagName
                        );


                    if(
                        sameTags.length > 1
                    ){

                        const index =
                            sameTags.indexOf(
                                el
                            ) + 1;


                        selector +=
                            ":nth-of-type(" +
                            index +
                            ")";

                    }

                }


                parts.unshift(
                    selector
                );


                el =
                    parent;

            }


            return parts.join(
                " > "
            );

        }


        // =================================================
        // إنشاء Selector
        // =================================================

        const uniqueSelector =
            getUniqueSelector(
                element
            );


        if(!uniqueSelector){

            console.warn(
                "Live Editor: لم يتم إنشاء Selector"
            );

            return;

        }


        // =================================================
        // البحث عن العنصر في iframe
        // =================================================

        let target = null;


        try{

            target =
                iframeDoc.querySelector(
                    uniqueSelector
                );

        }catch(error){

            console.error(
                "Live Editor Selector Error:",
                uniqueSelector,
                error
            );

            return;

        }


        if(!target){

            console.warn(
                "Live Editor: العنصر غير موجود:",
                uniqueSelector
            );

            return;

        }


        // =================================================
        // data-live-name
        // =================================================

        const liveName =
            getLiveName(element);


        target.setAttribute(
            "data-live-name",
            liveName
        );


        // =================================================
        // Selector الخاص بالمحرر
        // =================================================

        const editorSelector =
            getEditorSelector(element);


        // =================================================
        // تحديد العنصر بصرياً
        // =================================================

        target.setAttribute(
            "data-live-highlight",
            "1"
        );


        target.style.outline =
            "3px solid orange";


        


        // =====================================================
        // CSS LIVE EDITOR
        // =====================================================

        if(selectedClass){

            let cssResult = "";


            // =================================================
            // استخراج CSS
            // =================================================

            const matchedProperties =
                getElementCSS(
                    element
                );


            // =================================================
            // منع تكرار الخصائص
            // =================================================

            const uniqueProperties = [];


            matchedProperties.forEach(
                properties => {

                    if(
                        !uniqueProperties.includes(
                            properties
                        )
                    ){

                        uniqueProperties.push(
                            properties
                        );

                    }

                }
            );


            // =================================================
            // إنشاء CSS النهائي
            // =================================================

            cssResult =
                editorSelector +
                "{\n";


            uniqueProperties.forEach(
                properties => {

                    cssResult +=
                        properties +
                        "\n";

                }
            );


            // =================================================
            // لا توجد CSS
            // =================================================

            if(
                uniqueProperties.length === 0 ){

                cssResult +=
                    "    /* لا توجد CSS لهذا العنصر */\n";

            }


            cssResult +=
                "}\n";


            // =================================================
            // عرض CSS
            // =================================================

            selectedClass.value =
                cssResult;


            selectedClass.style.display =
                "block";

        }


        // =================================================
        // التمرير إلى العنصر
        // =================================================

        try{

            target.scrollIntoView({

                behavior:
                    "smooth",

                block:
                    "center"

            });

        }catch(error){

            target.scrollIntoView();

        }

    }


    // =====================================================
    // استخراج CSS الخاص بالعنصر المحدد
    // =====================================================

    function getElementCSS(sourceElement){

        const result = [];


        if(!pageCSS){
            return result;
        }


        // =================================================
        // التأكد أن pageCSS نص
        // =================================================

        let cssText = "";


        try{

            cssText =
                String(pageCSS);

        }catch(error){

            return result;

        }


        // =================================================
        // إزالة التعليقات
        // =================================================

        cssText =
            cssText.replace(
                /\/\*[\s\S]*?\*\//g,
                ""
            );


        // =================================================
        // قراءة CSS Rules
        // =================================================

        const ruleRegex =
            /([^{}]+)\{([^{}]*)\}/g;


        let match;


        while(
            (match =
                ruleRegex.exec(cssText))
        ){

            const selectorText =
                match[1].trim();


            const properties =
                match[2].trim();


            if(
                !selectorText ||
                !properties
            ){

                continue;

            }


            // =================================================
            // تقسيم selectors
            // =================================================

            const selectors =
                selectorText
                    .split(",")
                    .map(
                        selector =>
                            selector.trim()
                    )
                    .filter(Boolean);


            selectors.forEach(
                selector => {

                    // =================================================
                    // منع *
                    // =================================================

                    if(
    selector === "*" ||
    selector.includes("*")
){

    return;

}


                    // =================================================
                    // فحص التطابق
                    // =================================================

                    let matched = false;


                    try{

                        matched =
                            sourceElement.matches(
                                selector
                            );

                    }catch(error){

                        matched = false;

                    }


                    if(!matched){
                        return;
                    }


                    // =================================================
                    // إذا كان العنصر لديه class
                    // تجاهل selector الذي هو Tag فقط
                    // =================================================

                    const hasClass =
                        sourceElement.classList &&
                        sourceElement.classList.length;


                    const onlyTag =
                        /^[a-zA-Z][a-zA-Z0-9-]*$/
                            .test(
                                selector
                            );


                    if(
                        hasClass &&
                        onlyTag
                    ){

                        return;

                    }


                    // =================================================
                    // حفظ الخصائص فقط
                    // =================================================

                    if(
                        !result.includes(
                            properties
                        )
                    ){

                        result.push(
                            properties
                        );

                    }

                }
            );

        }


        return result;

    }
        // =====================================================
    // تحديث Live Preview
    // =====================================================

    function updatePreview(){

        const code =
            textarea.value;


        // =================================================
        // إزالة script من المعاينة
        // =================================================

        const previewCode =
            code.replace(
                /<script\b[^>]*>[\s\S]*?<\/script>/gi,
                ""
            );


        // =================================================
        // Parser
        // =================================================

        const parser =
            new DOMParser();


        const treeDocument =
            parser.parseFromString(
                code,
                "text/html"
            );


        // =================================================
        // تنظيف HTML Tree
        // =================================================

        treeContainer.innerHTML =
            "";


        // =================================================
        // تنظيف Tags
        // =================================================

        liveTags.innerHTML =
            "";


        // =================================================
        // استخراج أسماء Tags
        // =================================================

        const tags =
            new Set();


        treeDocument
            .querySelectorAll("*")
            .forEach(element => {

                const tag =
                    element.tagName.toLowerCase();


                if(
                    tag === "html" ||
                    tag === "head" ||
                    tag === "body"
                ){

                    return;

                }


                tags.add(tag);

            });


        // =================================================
        // إنشاء أزرار Tags
        // =================================================

        Array.from(tags)
            .sort()
            .forEach(tag => {

                const tagButton =
                    document.createElement(
                        "button"
                    );


                tagButton.type =
                    "button";


                tagButton.textContent =
                    "<" + tag + ">";


                tagButton.dataset.tag =
                    tag;


                tagButton.addEventListener(
                    "click",
                    function(event){

                        event.preventDefault();

                        event.stopPropagation();


                        highlightTag(tag);

                    }
                );


                liveTags.appendChild(
                    tagButton
                );

            });


        // =================================================
        // العناصر الرئيسية
        // =================================================

        const rootElements =
            Array.from(
                treeDocument.body.children
            )
            .filter(element => {

                const tag =
                    element.tagName.toLowerCase();


                return (
                    tag !== "html" &&
                    tag !== "head" &&
                    tag !== "body"
                );

            });


        // =================================================
        // بناء HTML Tree
        // =================================================

        rootElements.forEach(element => {

            buildHTMLTree(
                element,
                treeContainer
            );

        });


        // =================================================
        // Live Preview
        // =================================================

        const iframeDocument =
            iframe.contentDocument ||
            iframe.contentWindow.document;


        if(!iframeDocument){
            return;
        }


        // =================================================
        // كتابة HTML داخل iframe
        // =================================================

        iframeDocument.open();


        iframeDocument.write(`

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>


<!-- =====================================
     CSS خارجي
===================================== -->

${pageLinks}


<!-- =====================================
     CSS الصفحة
===================================== -->

<style>

${pageCSS}

</style>


<!-- =====================================
     CSS خاص بالـ Preview
===================================== -->

<style>

*{
    box-sizing:border-box;
}

html,
body{

    margin:0;
    padding:0;

    width:100%;
    min-height:100%;

}

body{

    font-family:
        Arial,
        sans-serif;

}

[data-live-highlight]{

    position:relative;

}

</style>

</head>


<body>


<!-- =====================================
     HTML
===================================== -->

${previewCode}


<!-- =====================================
     JavaScript الصفحة
===================================== -->

${pageJS}


</body>

</html>

`);


        iframeDocument.close();


        // =================================================
        // مزامنة data-live-name
        // =================================================

        syncLiveNames(
            treeDocument,
            iframeDocument
        );

    }


    // =====================================================
    // مزامنة data-live-name بين Tree و Preview
    // =====================================================

    function syncLiveNames(
        sourceDocument,
        targetDocument
    ){

        const sourceElements =
            sourceDocument.body
                .querySelectorAll("*");


        const targetElements =
            targetDocument.body
                .querySelectorAll("*");


        const length =
            Math.min(
                sourceElements.length,
                targetElements.length
            );


        for(
            let i = 0;
            i < length;
            i++
        ){

            const source =
                sourceElements[i];


            const target =
                targetElements[i];


            const liveName =
                getLiveName(
                    source
                );


            target.setAttribute(
                "data-live-name",
                liveName
            );

        }

    }


    // =====================================================
    // إنشاء HTML Tree
    // =====================================================

    function buildHTMLTree(
        element,
        container
    ){

        if(
            !element ||
            element.nodeType !==
                Node.ELEMENT_NODE
        ){

            return;

        }


        // =================================================
        // اسم Tag
        // =================================================

        const currentTag =
            element.tagName.toLowerCase();


        if(
            currentTag === "html" ||
            currentTag === "head" ||
            currentTag === "body"
        ){

            return;

        }


        // =================================================
        // إنشاء Tree Node
        // =================================================

        const nodeDiv =
            document.createElement(
                "div"
            );


        nodeDiv.className =
            "tree-node";


        // =================================================
        // إنشاء زر Tag
        // =================================================

        const tagButton =
            document.createElement(
                "button"
            );


        tagButton.type =
            "button";


        tagButton.className =
            "tree-tag";


        tagButton.dataset.tag =
            currentTag;


        // =================================================
        // اسم التاج
        // =================================================

        const tagName =
            document.createElement(
                "span"
            );


        tagName.className =
            "tree-tag-name";


        let tagText =
            "<" + currentTag;


        // =================================================
        // ID
        // =================================================

        if(element.id){

            tagText +=
                " #" +
                element.id;

        }


        // =================================================
        // CLASS
        // =================================================

        if(
            element.classList &&
            element.classList.length
        ){

            tagText +=
                " ." +
                Array.from(
                    element.classList
                ).join(" .");

        }


        tagText +=
            ">";


        tagName.textContent =
            tagText;


        tagButton.appendChild(
            tagName
        );


        // =================================================
        // الحصول على الأبناء
        // =================================================

        const children =
            Array.from(
                element.children
            )
            .filter(child => {

                const tag =
                    child.tagName.toLowerCase();


                return (
                    tag !== "html" &&
                    tag !== "head" &&
                    tag !== "body"
                );

            });


        // =================================================
        // عدد الأبناء
        // =================================================

        if(children.length > 0){

            const count =
                document.createElement(
                    "span"
                );


            count.className =
                "tree-count";


            count.textContent =
                children.length;


            tagButton.appendChild(
                count
            );

        }


        // =================================================
        // إضافة الزر
        // =================================================

        nodeDiv.appendChild(
            tagButton
        );


        // =================================================
        // الضغط على العنصر
        // =================================================

        tagButton.addEventListener(
            "click",
            function(event){

                event.preventDefault();

                event.stopPropagation();


                highlightElement(
                    element
                );

            }
        );


        // =================================================
        // الأبناء
        // =================================================

        if(children.length > 0){

            const childrenDiv =
                document.createElement(
                    "div"
                );


            childrenDiv.className =
                "tree-children";


            children.forEach(child => {

                buildHTMLTree(
                    child,
                    childrenDiv
                );

            });


            nodeDiv.appendChild(
                childrenDiv
            );


            // =================================================
            // Double Click لفتح وإغلاق الأبناء
            // =================================================

            tagButton.addEventListener(
    "dblclick",
    function(event){

        event.preventDefault();

        event.stopPropagation();

        childrenDiv.classList.toggle(
            "collapsed"
        );

    }
);
        }


        // =================================================
        // إضافة Node
        // =================================================

        container.appendChild(
            nodeDiv
        );

    }
    // =====================================================
// Live Update
// =====================================================

if(textarea){

    textarea.addEventListener(
        "input",
        updatePreview
    );

}


// =====================================================
// CSS LIVE EDITOR
// =====================================================

if(selectedClass){

    selectedClass.addEventListener(
        "input",
        function(){

            const css =
                selectedClass.value;


            const iframeDocument =
                iframe.contentDocument ||
                iframe.contentWindow.document;


            if(!iframeDocument){
                return;
            }


            // =================================================
            // البحث عن Style الخاص بالمحرر
            // =================================================

            let editorStyle =
                iframeDocument.getElementById(
                    "live-css-editor"
                );


            // =================================================
            // إنشاء Style إذا لم يكن موجوداً
            // =================================================

            if(!editorStyle){

                editorStyle =
                    iframeDocument.createElement(
                        "style"
                    );

                editorStyle.id =
                    "live-css-editor";


                iframeDocument.head.appendChild(
                    editorStyle
                );

            }


            // =================================================
            // تطبيق CSS مباشرة
            // =================================================

            editorStyle.textContent =
                css;

        }
    );

}


// =====================================================
// التشغيل الأول
// =====================================================

updatePreview();


// =====================================================
// التركيز على HTML textarea
// =====================================================

setTimeout(
    function(){

        textarea.focus();

    },
    100
);

}

</script>
