<?php

$html = file_get_contents("header.html");

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

/* ==========================================
   GLOBAL
========================================== */

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
   TOGGLE BUTTON
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
   TOGGLE TEXT
   الكتابة تأخذ 70%
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
   LEVEL NAME
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
   EDIT BUTTON
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
   TAGS LIST
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


/* =================================================
   LIVE EDITOR
================================================= */

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


/* =================================================
   LIVE EDITOR BOX
================================================= */

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


/* =================================================
   LIVE EDITOR HEADER
================================================= */

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


/* =================================================
   CLOSE
================================================= */

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


/* =================================================
   LIVE EDITOR BODY
================================================= */

.live-editor-body{

    flex:1;

    display:flex;

    min-height:0;

    min-width:0;

    overflow:hidden;
}


/* =================================================
   CODE PANEL
================================================= */

.code-panel{

    width:50%;

    height:100%;

    display:flex;

    flex-direction:column;

    min-width:0;

    min-height:0;

    overflow:hidden;
}


/* =================================================
   PANEL TITLE
================================================= */

.panel-title{

    flex-shrink:0;

    background:#1e293b;

    color:white;

    padding:10px 15px;

    font-weight:bold;
}


/* =================================================
   CODE WRAPPER
================================================= */

.code-wrapper{

    flex:1;

    display:flex;

    flex-direction:column;

    min-width:0;

    min-height:0;

    overflow:hidden;
}


/* =================================================
   CSS SELECTED CLASS
================================================= */

.selected-class{

    display:block;

    width:100%;

    height:40%;

    min-width:0;

    min-height:0;

    resize:none;

    border:0;

    border-bottom:1px solid #334155;

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


/* =================================================
   HTML CODE
================================================= */

.code-input{

    display:block;

    flex:1;

    width:100%;

    height:60%;

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


/* =================================================
   PREVIEW PANEL
================================================= */

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


/* =================================================
   LIVE PREVIEW
================================================= */

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


/* =================================================
   TREE PANEL
================================================= */

.tree-panel{

    position:absolute;

    top:40px;

    left:8px;

    width:calc(100% - 16px);

    height:230px;

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

    box-shadow:0 5px 20px rgba(0,0,0,.6);
}


/* =================================================
   TREE HEADER
================================================= */

.tree-panel .panel-title{

    height:32px;

    min-height:32px;

    width:100%;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:4px 7px;

    margin:0;

    font-size:12px;

    line-height:1.3;

    background:#111b35;
}


/* =================================================
   TREE CLOSE
================================================= */

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

    flex-shrink:0;
}


/* =================================================
   LIVE LEVELS
================================================= */

.live-levels{

    display:flex;

    gap:4px;

    padding:4px;

    min-width:0;

    overflow-x:auto;

    flex-shrink:0;
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

.live-levels button:hover{

    background:orange;

    color:#000;
}


/* =================================================
   LIVE TAGS
================================================= */

.live-tags{

    display:flex;

    gap:4px;

    padding:4px;

    min-width:0;

    overflow-x:auto;

    border-bottom:1px solid #1e293b;

    flex-shrink:0;
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

.live-tags button:hover{

    background:orange;

    color:#000;
}

.live-tags button.active{

    background:orange;

    color:#000;
}


/* =================================================
   HTML TREE
================================================= */

.html-tree{

    flex:1;

    width:100%;

    height:auto;

    min-width:0;

    min-height:0;

    overflow-y:auto;

    overflow-x:hidden;

    padding:4px;

    margin:0;
}


/* =================================================
   TREE NODE
================================================= */

.tree-node{

    width:100%;

    min-width:0;

    margin:0;

    padding:0;
}


/* =================================================
   TREE TAG
================================================= */

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


/* =================================================
   TREE TAG NAME
================================================= */

.tree-tag-name{

    flex:1;

    min-width:0;

    overflow-wrap:anywhere;

    word-break:break-word;
}


/* =================================================
   TREE COUNT
================================================= */

.tree-count{

    flex-shrink:0;

    margin-left:4px;

    background:#334155;

    color:white;

    padding:1px 4px;

    border-radius:4px;

    font-size:8px;

    line-height:1.2;
}


/* =================================================
   TREE CHILDREN
================================================= */

.tree-children{

    width:100%;

    margin:0;

    margin-left:8px;

    padding:0 0 0 5px;

    border-left:1px solid #334155;
}


/* =================================================
   COLLAPSED
================================================= */

.tree-children.collapsed{

    display:none;
}


/* =================================================
   TREE HIGHLIGHT
================================================= */

.tree-tag.highlight{

    background:orange;

    color:#000;
}


/* =================================================
   MOBILE
================================================= */

@media(max-width:700px){

    /* ==========================================
       LIVE EDITOR
    ========================================== */

    .live-editor{

        padding:0;
    }


    .live-editor-box{

        width:100%;

        height:100%;

        max-width:none;

        border-radius:0;
    }


    /* ==========================================
       BODY
    ========================================== */

    .live-editor-body{

        display:flex;

        flex-direction:column;

        width:100%;

        height:calc(100% - 55px);

        min-height:0;
    }


    /* ==========================================
       CODE PANEL
    ========================================== */

    .code-panel{

        width:100%;

        height:50%;

        min-height:0;

        flex:0 0 50%;

        border-bottom:1px solid #334155;
    }


    /* ==========================================
       CODE WRAPPER
    ========================================== */

    .code-wrapper{

        width:100%;

        height:100%;

        min-height:0;
    }


    /* ==========================================
       CSS EDITOR
    ========================================== */

    .selected-class{

        width:100%;

        height:40%;

        min-height:0;

        flex:0 0 40%;

        padding:10px;

        font-size:13px;

        overflow:auto;
    }


    /* ==========================================
       HTML EDITOR
    ========================================== */

    .code-input{

        width:100%;

        height:60%;

        min-height:0;

        flex:1;

        padding:10px;

        font-size:13px;

        overflow:auto;
    }


    /* ==========================================
       PREVIEW
    ========================================== */

    .preview-panel{

        width:100%;

        height:50%;

        min-height:0;

        flex:0 0 50%;

        border-left:0;

        border-top:0;

        position:relative;
    }


    /* ==========================================
       LIVE PREVIEW
    ========================================== */

    .live-preview{

        width:100%;

        height:100%;

        min-height:0;
    }


    /* ==========================================
       TREE
    ========================================== */

    .tree-panel{

        position:absolute;

        top:38px;

        left:5px;

        right:auto;

        width:calc(100% - 10px);

        height:210px;

        margin:0;

        padding:0;

        border-radius:8px;

        z-index:99999;
    }


    /* ==========================================
       TREE HEADER
    ========================================== */

    .tree-panel .panel-title{

        width:100%;

        height:30px;

        min-height:30px;

        padding:4px 7px;

        margin:0;

        font-size:11px;
    }


    /* ==========================================
       TREE CLOSE
    ========================================== */

    .tree-close{

        width:22px;

        height:22px;

        font-size:16px;
    }


    /* ==========================================
       LEVELS
    ========================================== */

    .live-levels{

        padding:3px;

        gap:3px;
    }


    .live-levels button{

        padding:3px 7px;

        font-size:10px;
    }


    /* ==========================================
       TAGS
    ========================================== */

    .live-tags{

        padding:3px;

        gap:3px;
    }


    .live-tags button{

        padding:3px 6px;

        font-size:9px;
    }


    /* ==========================================
       HTML TREE
    ========================================== */

    .html-tree{

        width:100%;

        height:auto;

        flex:1;

        margin:0;

        padding:4px;

        overflow-y:auto;

        overflow-x:hidden;
    }


    /* ==========================================
       TREE TAG
    ========================================== */

    .tree-tag{

        width:100%;

        padding:3px 5px;

        margin:1px 0;

        font-size:9px;

        line-height:1.25;
    }


    .tree-tag-name{

        font-size:13px;

        overflow-wrap:anywhere;

        word-break:break-word;
    }


    /* ==========================================
       COUNT
    ========================================== */

    .tree-count{

        font-size:8px;

        padding:1px 3px;
    }

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


        // إظهار الآباء
        let parent =
            node.parentElement.closest(".node");

        while(parent){

            parent.style.display = "block";

            parent =
                parent.parentElement.closest(".node");

        }


        // إظهار مسار children
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

    });

}


// =====================================================
// عرض Tag معين
// =====================================================

function showTag(tag){

    // إعادة ألوان الأزرار
    document.querySelectorAll(".toggle").forEach(button => {

        button.style.background = "#111b35";
        button.style.color = "white";

    });


    // إخفاء العقد
    document.querySelectorAll(".node").forEach(node => {

        node.style.display = "none";

    });


    // إخفاء التفاصيل
    document.querySelectorAll(".details").forEach(details => {

        details.style.display = "none";

    });


    // إخفاء children
    document.querySelectorAll(".children").forEach(children => {

        children.style.display = "none";

    });


    // البحث
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


            // إظهار الآباء
            let parent =
                node.parentElement.closest(".node");

            while(parent){

                parent.style.display = "block";

                parent =
                    parent.parentElement.closest(".node");

            }


            // إظهار مسار children
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

    // =================================================
    // الحصول على Node
    // =================================================

    const node = button.closest(".node");

    if(!node){
        return;
    }


    // =================================================
    // HTML الأصلي
    // =================================================

    const html = node.dataset.html;

    if(!html){

        console.error(
            "Live Editor: data-html غير موجود"
        );

        return;
    }


    // =================================================
    // إنشاء Modal
    // =================================================

    const modal = document.createElement("div");

    modal.className = "live-editor";


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


                <!-- HTML -->

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


                <!-- PREVIEW -->

                <div class="preview-panel">

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
    // إضافة Modal
    // =================================================

    document.body.appendChild(modal);


    // =================================================
    // العناصر
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

    const liveLevels =
        modal.querySelector(".live-levels");

    const closeButton =
        modal.querySelector(".live-close");

    const treePanel =
        modal.querySelector(".tree-panel");

    const treeClose =
        modal.querySelector(".tree-close");


    // =================================================
    // فحص
    // =================================================

    if(
        !textarea ||
        !selectedClass ||
        !iframe ||
        !treeContainer
    ){

        console.error(
            "Live Editor: عناصر المحرر غير موجودة"
        );

        modal.remove();

        return;
    }


    // =================================================
    // HTML
    // =================================================

    textarea.value = html;


    // =================================================
    // إغلاق
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
    // إغلاق Tree
    // =================================================

    if(treeClose && treePanel){

        treeClose.addEventListener(
            "click",
            function(event){

                event.preventDefault();

                event.stopPropagation();

                treePanel.style.display = "none";

            }
        );

    }


    // =================================================
    // عداد data-name
    // =================================================

    let liveNameCounter = 0;


    // =================================================
    // Selector خاص بالعنصر
    // =================================================

    function getEditorSelector(element){

        // =============================================
        // ID
        // =============================================

        if(element.id){

            return "#" +
                CSS.escape(element.id);

        }


        // =============================================
        // CLASS
        // =============================================

        if(
            element.classList &&
            element.classList.length
        ){

            const validClasses =
                Array.from(
                    element.classList
                )
                .filter(className => {

                    return /^[a-zA-Z_-][a-zA-Z0-9_-]*$/
                        .test(className);

                });


            if(validClasses.length){

                return "." +
                    validClasses
                        .map(className =>
                            CSS.escape(className)
                        )
                        .join(".");

            }

        }


        // =============================================
        // لا يوجد ID ولا CLASS
        // =============================================

        let name =
            element.getAttribute(
                "data-live-name"
            );


        if(!name){

            liveNameCounter++;

            name =
                "data-name" +
                liveNameCounter;

            element.setAttribute(
                "data-live-name",
                name
            );

        }


        return (
            '[data-live-name="' +
            name +
            '"]'
        );

    }


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


    // =================================================
    // تحديد عنصر في Preview
    // =================================================

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


        // =============================================
        // إزالة التحديد السابق
        // =============================================

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


        // =============================================
        // الحصول على نفس العنصر في iframe
        // =============================================

        const sourceElements =
            Array.from(
                element.ownerDocument.querySelectorAll("*")
            );


        const index =
            sourceElements.indexOf(element);


        const targetElements =
            iframeDoc.querySelectorAll("*");


        const target =
            targetElements[index];


        if(!target){
            return;
        }


        // =============================================
        // إضافة data-live-name إذا لزم
        // =============================================

        const selector =
            getEditorSelector(target);


        // =============================================
        // تحديد العنصر
        // =============================================

        target.setAttribute(
            "data-live-highlight",
            "1"
        );


        target.style.outline =
            "3px solid orange";


        target.style.backgroundColor =
            "rgba(255,165,0,.15)";


        // =============================================
        // استخراج CSS
        // =============================================

        let cssResult = "";


        function getElementCSS(element){

            const result = [];


            Array.from(
                iframeDoc.styleSheets
            ).forEach(sheet => {

                let rules;

                try{

                    rules = sheet.cssRules;

                }catch(error){

                    return;

                }


                if(!rules){
                    return;
                }


                function readRules(ruleList){

                    Array.from(
                        ruleList
                    ).forEach(rule => {

                        // =================================
                        // CSS Rule
                        // =================================

                        if(rule.selectorText){

                            const selectors =
                                rule.selectorText
                                    .split(",")
                                    .map(
                                        selector =>
                                            selector.trim()
                                    );


                            selectors.forEach(
                                ruleSelector => {

                                    let matched = false;


                                    try{

                                        matched =
                                            element.matches(
                                                ruleSelector
                                            );

                                    }catch(error){

                                        matched = false;

                                    }


                                    if(!matched){
                                        return;
                                    }


                                    const style =
                                        rule.style;


                                    if(!style){
                                        return;
                                    }


                                    let properties = "";


                                    for(
                                        let i = 0;
                                        i < style.length;
                                        i++
                                    ){

                                        const property =
                                            style[i];


                                        const value =
                                            style.getPropertyValue(
                                                property
                                            );


                                        const priority =
                                            style.getPropertyPriority(
                                                property
                                            );


                                        if(value){

                                            properties +=
                                                "    " +
                                                property +
                                                ": " +
                                                value;


                                            if(priority){

                                                properties +=
                                                    " !" +
                                                    priority;

                                            }


                                            properties +=
                                                ";\n";

                                        }

                                    }


                                    if(!properties){
                                        return;
                                    }


                                    const key =
                                        ruleSelector +
                                        "{" +
                                        properties +
                                        "}";


                                    if(
                                        result.some(
                                            item =>
                                                item.key === key
                                        )
                                    ){

                                        return;

                                    }


                                    result.push({

                                        key:key,

                                        selector:
                                            ruleSelector,

                                        properties:
                                            properties

                                    });

                                }
                            );

                        }


                        // =================================
                        // Media / Supports
                        // =================================

                        if(rule.cssRules){

                            try{

                                readRules(
                                    rule.cssRules
                                );

                            }catch(error){}

                        }

                    });

                }


                readRules(rules);

            });


            return result;

        }


        const matchedRules =
            getElementCSS(target);


        // =============================================
        // CSS الحقيقي
        // =============================================

        matchedRules.forEach(rule => {

            cssResult +=
                rule.selector +
                "{\n";

            cssResult +=
                rule.properties;

            cssResult +=
                "}\n\n";

        });


        // =============================================
        // إذا لم توجد قاعدة CSS
        // =============================================

        if(!cssResult){

            cssResult =
                selector +
                "{\n" +
                "    /* اكتب CSS هنا */\n" +
                "}\n";

        }


        // =============================================
        // CSS
        // =============================================

        selectedClass.value =
            cssResult;


        selectedClass.style.display =
            "block";


        // =============================================
        // Scroll
        // =============================================

        try{

            target.scrollIntoView({

                behavior:"smooth",

                block:"center"

            });

        }catch(error){

            target.scrollIntoView();

        }

    }


    // =================================================
    // بناء Preview
    // =================================================

    function updatePreview(){

        const code =
            textarea.value;


        // =============================================
        // حذف Script من Preview
        // =============================================

        const previewCode =
            code.replace(
                /<script\b[^>]*>[\s\S]*?<\/script>/gi,
                ""
            );


        // =============================================
        // DOM Parser
        // =============================================

        const parser =
            new DOMParser();


        const treeDocument =
            parser.parseFromString(
                code,
                "text/html"
            );


        // =============================================
        // تنظيف Tree
        // =============================================

        treeContainer.innerHTML = "";


        liveTags.innerHTML = "";


        liveLevels.innerHTML = "";


        // =============================================
        // Tags
        // =============================================

        const tags =
            new Set();


        treeDocument
            .querySelectorAll("*")
            .forEach(element => {

                const tag =
                    element.tagName.toLowerCase();


                if(
                    tag !== "html" &&
                    tag !== "head" &&
                    tag !== "body"
                ){

                    tags.add(tag);

                }

            });


        // =============================================
        // أزرار Tags
        // =============================================

        Array.from(tags)
            .sort()
            .forEach(tag => {

                const tagButton =
                    document.createElement(
                        "button"
                    );


                tagButton.type = "button";


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


        // =============================================
        // Root
        // =============================================

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


        // =============================================
        // Tree
        // =============================================

        rootElements.forEach(element => {

            buildHTMLTree(
                element,
                treeContainer
            );

        });


        // =============================================
        // iframe
        // =============================================

        const iframeDocument =
            iframe.contentDocument ||
            iframe.contentWindow.document;


        if(!iframeDocument){
            return;
        }


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

${typeof pageLinks !== "undefined" ? pageLinks : ""}

<style>

${typeof pageCSS !== "undefined" ? pageCSS : ""}

</style>


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

[data-live-highlight]{

    position:relative;

}

</style>

</head>


<body>

${previewCode}

${typeof pageJS !== "undefined" ? pageJS : ""}

</body>

</html>

`);


        iframeDocument.close();

    }


    // =================================================
    // HTML TREE
    // =================================================

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


        const currentTag =
            element.tagName.toLowerCase();


        if(
            currentTag === "html" ||
            currentTag === "head" ||
            currentTag === "body"
        ){

            return;

        }


        const nodeDiv =
            document.createElement(
                "div"
            );


        nodeDiv.className =
            "tree-node";


        // =============================================
        // زر Tag
        // =============================================

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


        // =============================================
        // اسم Tag
        // =============================================

        const tagName =
            document.createElement(
                "span"
            );


        tagName.className =
            "tree-tag-name";


        let tagText =
            "<" + currentTag;


        // =============================================
        // ID
        // =============================================

        if(element.id){

            tagText +=
                " #" +
                element.id;

        }


        // =============================================
        // CLASS
        // =============================================

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


        tagText += ">";


        tagName.textContent =
            tagText;


        tagButton.appendChild(
            tagName
        );


        // =============================================
        // Children
        // =============================================

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


        nodeDiv.appendChild(
            tagButton
        );


        // =============================================
        // Click
        // =============================================

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


        // =============================================
        // Children
        // =============================================

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


            // =========================================
            // Double Click
            // =========================================

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


        container.appendChild(
            nodeDiv
        );

    }


    // =================================================
    // HTML Live Update
    // =================================================

    textarea.addEventListener(
        "input",
        updatePreview
    );


    // =================================================
    // CSS Live Update
    // =================================================

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


            let editorStyle =
                iframeDocument.getElementById(
                    "live-css-editor"
                );


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


            editorStyle.textContent =
                css;

        }
    );


    // =================================================
    // تشغيل أول مرة
    // =================================================

    updatePreview();


    // =================================================
    // التركيز
    // =================================================

    setTimeout(
        function(){

            textarea.focus();

        },
        100
    );

}

</script>
  

