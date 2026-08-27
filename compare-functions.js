const fs = require("fs");

const webref = JSON.parse(
    fs.readFileSync("webref-functions.json", "utf8")
);

const allCss = JSON.parse(
    fs.readFileSync("all-css.json", "utf8")
);


/* ================================
   GET FUNCTION NAME
================================ */

function getFunctionName(value) {

    if (typeof value !== "string") {
        return null;
    }

    const match = value.match(
        /^([a-zA-Z_][a-zA-Z0-9_-]*)\(\)$/
    );

    if (!match) {
        return null;
    }

    return match[1].toLowerCase();
}


/* ================================
   WEBREF FUNCTIONS
================================ */

const webrefFunctions = new Set();

for (const item of webref) {

    const name = getFunctionName(item.name);

    if (name) {
        webrefFunctions.add(name);
    }
}


/* ================================
   ALL-CSS FUNCTIONS
================================ */

const allCssFunctions = new Set();

function scan(value) {

    if (typeof value === "string") {

        const regex =
            /\b([a-zA-Z_][a-zA-Z0-9_-]*)\s*\(/g;

        let match;

        while ((match = regex.exec(value)) !== null) {

            allCssFunctions.add(
                match[1].toLowerCase()
            );
        }

        return;
    }


    if (Array.isArray(value)) {

        for (const item of value) {
            scan(item);
        }

        return;
    }


    if (value && typeof value === "object") {

        for (const [key, val] of Object.entries(value)) {

            scan(key);
            scan(val);

        }
    }
}


scan(allCss);


/* ================================
   DIFFERENCE
================================ */

const newFunctions = [
    ...webrefFunctions
]
.filter(
    name => !allCssFunctions.has(name)
)
.sort();


/* ================================
   PRINT
================================ */

console.log(
    "================================"
);

console.log(
    "WEBREF UNIQUE:",
    webrefFunctions.size
);

console.log(
    "ALL-CSS FUNCTIONS:",
    allCssFunctions.size
);

console.log(
    "NOT IN ALL-CSS:",
    newFunctions.length
);

console.log(
    "================================"
);


for (let i = 0; i < newFunctions.length; i++) {

    console.log(
        `${i + 1}. ${newFunctions[i]}()`
    );
}


/* ================================
   SAVE
================================ */

fs.writeFileSync(
    "new-functions.json",
    JSON.stringify(
        newFunctions,
        null,
        2
    )
);

console.log(
    "================================"
);

console.log(
    "Saved: new-functions.json"
);

console.log(
    "================================"
);
