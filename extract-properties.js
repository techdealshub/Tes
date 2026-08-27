const fs = require("fs");

const inputFile = "all-css.json";
const outputFile = "properties-base.json";

const data = JSON.parse(
    fs.readFileSync(inputFile, "utf8")
);

const properties = Array.isArray(data)
    ? data
    : data.properties || [];

function extractFunctions(syntax) {

    const result = new Set();

    if (!syntax) return [];

    const re = /([a-zA-Z][a-zA-Z0-9-]*)\s*\(/g;

    let m;

    while ((m = re.exec(syntax))) {
        result.add(m[1] + "()");
    }

    return [...result];
}

function extractTypes(syntax) {

    const result = new Set();

    if (!syntax) return [];

    const re = /<([^>]+)>/g;

    let m;

    while ((m = re.exec(syntax))) {
        result.add("<" + m[1] + ">");
    }

    return [...result];
}

const result = properties.map((p, i) => {

    const syntax =
        typeof p.syntax === "string"
            ? p.syntax
            : "";

    return {

        id: i + 1,

        name: p.name ?? "",

        syntax,

        initial: p.initial ?? null,

        appliesTo: p.appliesTo ?? null,

        inherited: p.inherited ?? null,

        percentages: p.percentages ?? null,

        computedValue: p.computedValue ?? null,

        animationType: p.animationType ?? null,

        canonicalOrder: p.canonicalOrder ?? null,

        href: p.href ?? null,

        hasSyntax: syntax.trim().length > 0,

        functions: extractFunctions(syntax),

        types: extractTypes(syntax)

    };
});

const output = {
    total: result.length,
    properties: result
};

fs.writeFileSync(
    outputFile,
    JSON.stringify(output, null, 2)
);

console.log("================================");
console.log("INPUT PROPERTIES :", properties.length);
console.log("OUTPUT PROPERTIES:", result.length);
console.log("================================");
console.log("Saved:", outputFile);
