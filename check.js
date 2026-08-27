
/* =========================================================
   CONFIG
========================================================= */

const TARGET = document.getElementById("target");

const PROPERTIES_FILE = "properties-values.json";

const results = [];

let totalTests = 0;
let completed = 0;


/* =========================================================
   LOAD JSON
========================================================= */

async function loadJSON(url){

    const response = await fetch(url);

    if(!response.ok){
        throw new Error(
            "Cannot load " + url +
            " HTTP " + response.status
        );
    }

    return await response.json();
}


/* =========================================================
   MEASURE ELEMENT
========================================================= */

function measure(el){

    const r = el.getBoundingClientRect();

    return {
        left: r.left,
        top: r.top,
        width: r.width,
        height: r.height,
        right: r.right,
        bottom: r.bottom
    };
}


/* =========================================================
   DIFFERENCE
========================================================= */

function difference(a,b){

    const eps = 0.01;

    return (
        Math.abs(a.left   - b.left)   > eps ||
        Math.abs(a.top    - b.top)    > eps ||
        Math.abs(a.width  - b.width)  > eps ||
        Math.abs(a.height - b.height) > eps ||
        Math.abs(a.right  - b.right)  > eps ||
        Math.abs(a.bottom - b.bottom) > eps
    );
}


/* =========================================================
   NORMALIZE VALUE
========================================================= */

function normalizeValue(value){

    if(value == null)
        return "";

    return String(value).trim();
}


/* =========================================================
   FIND FUNCTION NAMES
========================================================= */

function hasFunction(value){

    return /[a-zA-Z-]+\s*\(/.test(value);
}


/* =========================================================
   CREATE NUMERIC TEST VALUES
========================================================= */

function numericVariants(value){

    const v = String(value).trim();

    const match = v.match(
        /^(-?(?:\d+(?:\.\d+)?|\.\d+))([a-zA-Z%]+)$/
    );

    if(!match)
        return [v];

    const unit = match[2];

    return [
        "1" + unit,
        "2" + unit,
        "10" + unit,
        "50" + unit,
        "100" + unit
    ];
}


/* =========================================================
   VALUE LIST
========================================================= */

function expandValues(value){

    value = normalizeValue(value);

    if(!value)
        return [];

    const clean = value.replace(/\s/g, "");

    const match = clean.match(
        /^(-?(?:\d+(?:\.\d+)?|\.\d+))(px|%|em|rem|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q)$/i
    );

    if(!match)
        return [value];

    const unit = match[2];

    return [
        "1" + unit,
        "2" + unit,
        "10" + unit,
        "50" + unit,
        "100" + unit
    ];
}    /*
       إعادة القواعد الأساسية المهمة
    */

    TARGET.style.width = "200px";
    TARGET.style.height = "100px";
    TARGET.style.margin = "50px";
    TARGET.style.padding = "10px";
    TARGET.style.border = "2px solid white";
    TARGET.style.background = "#1e293b";
    TARGET.style.position = "relative";
}


/* =========================================================
   GET COMPUTED VALUE
========================================================= */

function getComputed(property){

    const cs = getComputedStyle(TARGET);

    return cs.getPropertyValue(property).trim();
}


/* =========================================================
   TEST ONE VALUE
========================================================= */

function testValue(property,value){

    resetTarget();

    /*
       CSS.supports لا يعني أن القيمة أثرت.
    */

    let supports = false;

    try{

        supports = CSS.supports(
            property,
            value
        );

    }catch(e){

        supports = false;
    }

    if(!supports){

        return {
            property,
            value,
            supports:false,
            computed:false,
            effective:false,
            reason:"CSS.supports=false"
        };
    }


    /*
       القياس قبل
    */

    const before = measure(TARGET);


    /*
       تطبيق القيمة
    */

    try{

        TARGET.style.setProperty(
            property,
            value
        );

    }catch(e){

        return {
            property,
            value,
            supports:true,
            computed:false,
            effective:false,
            reason:"setProperty error"
        };
    }


    /*
       إجبار المتصفح على حساب CSS
    */

    void TARGET.offsetWidth;


    /*
       computed
    */

    const computed = getComputed(property);


    /*
       إذا لم يقبلها المتصفح فعليًا
    */

    const inline = TARGET.style.getPropertyValue(property).trim();


    if(!inline){

        return {
            property,
            value,
            supports:true,
            computed:false,
            effective:false,
            reason:"value rejected"
        };
    }


    /*
       القياس بعد
    */

    const after = measure(TARGET);


    const changed = difference(
        before,
        after
    );


    return {

        property,

        value,

        supports:true,

        computed: !!computed,

        computedValue: computed,

        effective: changed,

        before,

        after,

        reason:
            changed
            ? "layout changed"
            : "no layout change"
    };
}


/* =========================================================
   TEST PROPERTY
========================================================= */

function testProperty(property,values){

    const output = [];

    for(const originalValue of values){

        const variants =
            expandValues(originalValue);

        for(const value of variants){

            totalTests++;

            const result =
                testValue(
                    property,
                    value
                );

            completed++;

            output.push(result);

            renderProgress();
        }
    }

    return output;
}


/* =========================================================
   PROGRESS
========================================================= */

function renderProgress(){

    const percent =
        totalTests
        ? Math.round(
            completed /
            totalTests *
            100
        )
        : 0;

    document.getElementById("status")
        .firstChild.textContent =
        `Testing: ${completed} / ${totalTests} (${percent}%)`;

    document.getElementById("progress").value =
        percent;
}


/* =========================================================
   RENDER RESULT
========================================================= */

function renderResult(result){

    const div =
        document.createElement("div");

    div.className =
        "item " +
        (
            result.effective
            ? "good"
            : result.supports
                ? "bad"
                : "skip"
        );

    div.textContent =
        result.property +
        ": " +
        result.value +
        " → " +
        (
            result.effective
            ? "EFFECTIVE"
            : result.supports
                ? "SUPPORTED / NO EFFECT"
                : "NOT SUPPORTED"
        );

    document.getElementById("results")
        .appendChild(div);
}


/* =========================================================
   COUNT TESTS FIRST
========================================================= */

function countTests(data){

    let count = 0;

    for(const item of data){

        const property =
            item.name ||
            item.property;

        let values =
            item.values ||
            item.allowedValues ||
            item.testValues ||
            [];

        if(!Array.isArray(values))
            values = [values];

        for(const value of values){

            count +=
                expandValues(value).length;
        }
    }

    return count;
}


/* =========================================================
   SAVE RESULTS
========================================================= */

function downloadJSON(){

    const blob =
        new Blob(
            [
                JSON.stringify(
                    results,
                    null,
                    2
                )
            ],
            {
                type:"application/json"
            }
        );

    const a =
        document.createElement("a");

    a.href =
        URL.createObjectURL(blob);

    a.download =
        "tested-properties.json";

    a.click();
}


/* =========================================================
   MAIN
========================================================= */

async function main(){

    try{

        const data =
            await loadJSON(
                PROPERTIES_FILE
            );


        /*
           properties-values.json
           قد يكون:

           [
             {...},
             {...}
           ]

           أو:

           {
             properties:[...]
           }
        */

        const properties =
            Array.isArray(data)
            ? data
            : data.properties || [];


        /*
           حساب العدد
        */

        totalTests =
            countTests(
                properties
            );


        document.getElementById("status")
            .firstChild.textContent =
            `Starting: ${totalTests} tests`;


        /*
           الاختبار
        */

        for(const item of properties){

            const property =
                item.name ||
                item.property;

            if(!property)
                continue;


            let values =
                item.values ||
                item.allowedValues ||
                item.testValues ||
                [];


            if(!Array.isArray(values))
                values = [values];


            /*
               إذا لم توجد قيم
            */

            if(values.length === 0){

                results.push({

                    property,

                    value:null,

                    supports:false,

                    computed:false,

                    effective:false,

                    reason:"NO TEST VALUES"

                });

                continue;
            }


            const propertyResults =
                testProperty(
                    property,
                    values
                );


            results.push(
                ...propertyResults
            );


            /*
               عرض النتيجة بعد كل property
            */

            for(
                const r
                of propertyResults
            ){

                renderResult(r);
            }
        }


        /*
           حفظ تلقائي
        */

        localStorage.setItem(
            "tested-properties",
            JSON.stringify(
                results
            )
        );


        console.log(
            "================================"
        );

        console.log(
            "PROPERTIES:",
            properties.length
        );

        console.log(
            "TESTS:",
            results.length
        );

        console.log(
            "EFFECTIVE:",
            results.filter(
                x => x.effective
            ).length
        );

        console.log(
            "SUPPORTED NO EFFECT:",
            results.filter(
                x =>
                    x.supports &&
                    !x.effective
            ).length
        );

        console.log(
            "NOT SUPPORTED:",
            results.filter(
                x => !x.supports
            ).length
        );

        console.log(
            "================================"
        );


        document.getElementById("status")
            .firstChild.textContent =
            `DONE — ${results.length} tests`;


        /*
           زر حفظ
        */

        const button =
            document.createElement("button");

        button.textContent =
            "Save tested-properties.json";

        button.style.padding =
            "12px";

        button.style.margin =
            "20px 0";

        button.onclick =
            downloadJSON;

        document.body.prepend(
            button
        );


    }catch(error){

        console.error(error);

        document.getElementById("status")
            .textContent =
            "ERROR: " +
            error.message;
    }
}


main();

