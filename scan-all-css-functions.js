const fs = require("fs");

const data = JSON.parse(
    fs.readFileSync("all-css.json", "utf8")
);

console.log("================================");

console.log(
    "PROPERTIES TYPE:",
    Array.isArray(data.properties)
        ? "ARRAY"
        : typeof data.properties
);

console.log(
    "PROPERTIES COUNT:",
    data.properties.length
);

console.log(
    "FUNCTIONS TYPE:",
    Array.isArray(data.functions)
        ? "ARRAY"
        : typeof data.functions
);

console.log(
    "FUNCTIONS COUNT:",
    data.functions.length
);

console.log("================================");

console.log("\nFIRST PROPERTIES:");

for (
    let i = 0;
    i < Math.min(20, data.properties.length);
    i++
) {

    const p = data.properties[i];

    console.log(
        i,
        "NAME:",
        p.name,
        "SYNTAX:",
        p.syntax
    );
}

console.log("\n================================");
console.log("FUNCTIONS:");
console.log("================================");

for (const f of data.functions) {

    console.log(f.name);

}
