const fs = require("fs");

const INPUT = "properties-base.json";
const OUTPUT = "properties-values.json";

const data = JSON.parse(
    fs.readFileSync(INPUT, "utf8")
);

const properties = data.properties || [];


/* ==============================
   قيم عامة
============================== */

const numericValues = [
    "1px",
    "1%",
    "1em",
    "1rem",
    "1ex",
    "1ch",
    "1vw",
    "1vh",
    "1vmin",
    "1vmax",
    "1cm",
    "1mm",
    "1Q",
    "1in",
    "1pt",
    "1pc",
    "1cap",
    "1lh",
    "1rlh",
    "1svw",
    "1svh",
    "1lvw",
    "1lvh",
    "1dvw",
    "1dvh"
];

const numberValues = [
    "0",
    "1",
    "2",
    "0.5",
    "-1"
];

const integerValues = [
    "0",
    "1",
    "2",
    "3"
];

const angleValues = [
    "1deg",
    "1grad",
    "1rad",
    "1turn"
];

const timeValues = [
    "1s",
    "1ms"
];

const frequencyValues = [
    "1Hz",
    "1kHz"
];

const resolutionValues = [
    "1dpi",
    "1dpcm",
    "1dppx"
];

const colorValues = [
    "red",
    "rgb(1, 1, 1)",
    "rgba(1, 1, 1, 1)",
    "hsl(1, 1%, 1%)",
    "transparent"
];

const globalValues = [
    "initial",
    "inherit",
    "unset",
    "revert",
    "revert-layer"
];


/* ==============================
   Functions
============================== */

const functionValues = {

    "calc()": [
        "calc(1px + 1px)",
        "calc(1% + 1px)"
    ],

    "calc-size()": [
        "calc-size(auto, size)",
        "calc-size(max-content, size)"
    ],

    "min()": [
        "min(1px, 2px)",
        "min(1%, 1px)"
    ],

    "max()": [
        "max(1px, 2px)",
        "max(1%, 1px)"
    ],

    "clamp()": [
        "clamp(1px, 2px, 3px)",
        "clamp(1%, 2%, 3%)"
    ],

    "minmax()": [
        "minmax(1px, 2px)"
    ],

    "fit-content()": [
        "fit-content(1px)",
        "fit-content(10%)"
    ],

    "repeat()": [
        "repeat(2, 1fr)"
    ],

    "circle()": [
        "circle(1px)",
        "circle(1%)"
    ],

    "ellipse()": [
        "ellipse(1px 2px)"
    ],

    "inset()": [
        "inset(1px)"
    ],

    "rect()": [
        "rect(1px, 2px, 3px, 4px)"
    ],

    "xywh()": [
        "xywh(1px 1px 1px 1px)"
    ],

    "polygon()": [
        "polygon(0 0, 1px 0, 1px 1px)"
    ],

    "path()": [
        'path("M 0 0 L 1 1")'
    ],

    "shape()": [
        "shape(from 0 0, line to 1px 1px)"
    ],

    "superellipse()": [
        "superellipse(1)"
    ],

    "ray()": [
        "ray(1deg)"
    ],

    "rgb()": [
        "rgb(1 1 1)"
    ],

    "hsl()": [
        "hsl(1 1% 1%)"
    ],

    "hwb()": [
        "hwb(1 1% 1%)"
    ],

    "lab()": [
        "lab(1% 1 1)"
    ],

    "lch()": [
        "lch(1% 1 1)"
    ],

    "oklab()": [
        "oklab(1% 1 1)"
    ],

    "oklch()": [
        "oklch(1% 1 1)"
    ],

    "color()": [
        "color(srgb 1 1 1)"
    ],

    "color-mix()": [
        "color-mix(in srgb, red, blue)"
    ],

    "light-dark()": [
        "light-dark(white, black)"
    ],

    "blur()": [
        "blur(1px)"
    ],

    "brightness()": [
        "brightness(1)"
    ],

    "contrast()": [
        "contrast(1)"
    ],

    "grayscale()": [
        "grayscale(1)"
    ],

    "hue-rotate()": [
        "hue-rotate(1deg)"
    ],

    "invert()": [
        "invert(1)"
    ],

    "opacity()": [
        "opacity(1)"
    ],

    "saturate()": [
        "saturate(1)"
    ],

    "sepia()": [
        "sepia(1)"
    ],

    "drop-shadow()": [
        "drop-shadow(1px 1px 1px black)"
    ],

    "translate()": [
        "translate(1px)",
        "translate(1px, 1px)"
    ],

    "translateX()": [
        "translateX(1px)"
    ],

    "translateY()": [
        "translateY(1px)"
    ],

    "translateZ()": [
        "translateZ(1px)"
    ],

    "translate3d()": [
        "translate3d(1px, 1px, 1px)"
    ],

    "rotate()": [
        "rotate(1deg)"
    ],

    "rotateX()": [
        "rotateX(1deg)"
    ],

    "rotateY()": [
        "rotateY(1deg)"
    ],

    "rotateZ()": [
        "rotateZ(1deg)"
    ],

    "rotate3d()": [
        "rotate3d(1, 1, 1, 1deg)"
    ],

    "scale()": [
        "scale(1.1)"
    ],

    "scaleX()": [
        "scaleX(1.1)"
    ],

    "scaleY()": [
        "scaleY(1.1)"
    ],

    "scaleZ()": [
        "scaleZ(1.1)"
    ],

    "scale3d()": [
        "scale3d(1.1, 1.1, 1.1)"
    ],

    "skew()": [
        "skew(1deg)"
    ],

    "skewX()": [
        "skewX(1deg)"
    ],

    "skewY()": [
        "skewY(1deg)"
    ],

    "perspective()": [
        "perspective(1px)"
    ],

    "matrix()": [
        "matrix(1, 0, 0, 1, 1, 1)"
    ],

    "matrix3d()": [
        "matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1)"
    ],

    "linear-gradient()": [
        "linear-gradient(red, blue)"
    ],

    "radial-gradient()": [
        "radial-gradient(red, blue)"
    ],

    "conic-gradient()": [
        "conic-gradient(red, blue)"
    ],

    "repeating-linear-gradient()": [
        "repeating-linear-gradient(red, blue)"
    ],

    "repeating-radial-gradient()": [
        "repeating-radial-gradient(red, blue)"
    ],

    "repeating-conic-gradient()": [
        "repeating-conic-gradient(red, blue)"
    ],

    "image()": [
        "image(url(test.png))"
    ],

    "image-set()": [
        "image-set(url(test.png) 1x)"
    ],

    "cross-fade()": [
        "cross-fade(url(test.png), url(test.png), 50%)"
    ],

    "url()": [
        "url(test.png)"
    ],

    "var()": [
        "var(--test)"
    ],

    "attr()": [
        "attr(data-test)"
    ],

    "env()": [
        "env(safe-area-inset-top)"
    ],

    "counter()": [
        "counter(test)"
    ],

    "counters()": [
        "counters(test, '.')"
    ],

    "symbols()": [
        "symbols(numeric)"
    ],

    "linear()": [
        "linear(0, 1)"
    ],

    "cubic-bezier()": [
        "cubic-bezier(0, 0, 1, 1)"
    ],

    "steps()": [
        "steps(2)"
    ],

    "scroll()": [
        "scroll()"
    ],

    "view()": [
        "view()"
    ],

    "anchor()": [
        "anchor(top)"
    ],

    "anchor-size()": [
        "anchor-size(width)"
    ],

    "sibling-index()": [
        "sibling-index()"
    ],

    "sibling-count()": [
        "sibling-count()"
    ],

    "abs()": [
        "abs(1)"
    ],

    "sign()": [
        "sign(1)"
    ],

    "sin()": [
        "sin(1deg)"
    ],

    "cos()": [
        "cos(1deg)"
    ],

    "tan()": [
        "tan(1deg)"
    ],

    "asin()": [
        "asin(1)"
    ],

    "acos()": [
        "acos(1)"
    ],

    "atan()": [
        "atan(1)"
    ],

    "atan2()": [
        "atan2(1, 1)"
    ],

    "pow()": [
        "pow(1, 1)"
    ],

    "sqrt()": [
        "sqrt(1)"
    ],

    "hypot()": [
        "hypot(1, 1)"
    ],

    "log()": [
        "log(1)"
    ],

    "exp()": [
        "exp(1)"
    ],

    "mod()": [
        "mod(1, 1)"
    ],

    "rem()": [
        "rem(1, 1)"
    ],

    "round()": [
        "round(1px)"
    ],

    "progress()": [
        "progress(1, 0, 1)"
    ]

};


/* ==============================
   استخراج القيم من syntax
============================== */

function syntaxKeywords(syntax) {

    if (!syntax) return [];

    const values = [];

    /*
       نلتقط الكلمات الموجودة في syntax
       ونستبعد <types> والدوال
    */

    const cleaned = syntax
        .replace(/<[^>]+>/g, " ")
        .replace(/\([^)]*\)/g, " ");

    const tokens = cleaned.match(
        /(?<![-\w])[-a-zA-Z][-\w]*(?![-\w])/g
    ) || [];

    for (const token of tokens) {

        if (
            token !== "and" &&
            token !== "or" &&
            token !== "to" &&
            token !== "from"
        ) {
            values.push(token);
        }
    }

    return values;
}


/* ==============================
   تحديد أنواع syntax
============================== */

function hasType(syntax, type) {

    return syntax.includes("<" + type + ">");
}


/* ==============================
   بناء القيم
============================== */

function generateValues(property) {

    const syntax = property.syntax || "";

    const values = new Set();


    /*
       1. الكلمات الموجودة في syntax
    */

    for (const value of syntaxKeywords(syntax)) {
        values.add(value);
    }


    /*
       2. Global values
    */

    for (const value of globalValues) {
        values.add(value);
    }


    /*
       3. Numeric
    */

    if (
        /<length|<percentage|<length-percentage/.test(syntax)
    ) {
        for (const value of numericValues) {
            values.add(value);
        }
    }


    /*
       4. number
    */

    if (hasType(syntax, "number")) {

        for (const value of numberValues) {
            values.add(value);
        }
    }


    /*
       5. integer
    */

    if (hasType(syntax, "integer")) {

        for (const value of integerValues) {
            values.add(value);
        }
    }


    /*
       6. angle
    */

    if (hasType(syntax, "angle")) {

        for (const value of angleValues) {
            values.add(value);
        }
    }


    /*
       7. time
    */

    if (hasType(syntax, "time")) {

        for (const value of timeValues) {
            values.add(value);
        }
    }


    /*
       8. frequency
    */

    if (hasType(syntax, "frequency")) {

        for (const value of frequencyValues) {
            values.add(value);
        }
    }


    /*
       9. resolution
    */

    if (hasType(syntax, "resolution")) {

        for (const value of resolutionValues) {
            values.add(value);
        }
    }


    /*
       10. color
    */

    if (
        syntax.includes("<color>") ||
        syntax.includes("<color-base>")
    ) {

        for (const value of colorValues) {
            values.add(value);
        }
    }


    /*
       11. functions
    */

    for (const fn of property.functions || []) {

        const list = functionValues[fn];

        if (list) {

            for (const value of list) {
                values.add(value);
            }

        }
    }


    return [...values];
}


/* ==============================
   إنشاء الناتج
============================== */

const result = properties.map(property => {

    const values = generateValues(property);

    return {

        ...property,

        values,

        valuesCount: values.length

    };

});


/* ==============================
   حفظ
============================== */

fs.writeFileSync(
    OUTPUT,
    JSON.stringify(
        {
            total: result.length,
            properties: result
        },
        null,
        2
    )
);


console.log("================================");
console.log("PROPERTIES :", properties.length);
console.log("================================");

console.log(
    "WITH VALUES :",
    result.filter(p => p.values.length > 0).length
);

console.log(
    "WITHOUT VALUES :",
    result.filter(p => p.values.length === 0).length
);

console.log(
    "TOTAL TEST VALUES :",
    result.reduce(
        (n, p) => n + p.values.length,
        0
    )
);

console.log("================================");
console.log("Saved:", OUTPUT);

