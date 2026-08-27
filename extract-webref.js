const webref = require("@webref/css");
const fs = require("fs");

async function main() {

    console.log("Loading Webref...");

    const data = await webref.listAll();

    console.log("Webref loaded");

    const functions =
        (data.functions || [])
        .map(fn => ({
            name: fn.name,
            href: fn.href || "",
            syntax: fn.syntax || "",
            for: fn.for || []
        }))
        .filter(fn =>
            typeof fn.name === "string"
        )
        .sort((a, b) =>
            a.name.localeCompare(b.name)
        );

    fs.writeFileSync(
        "webref-functions.json",
        JSON.stringify(
            functions,
            null,
            2
        )
    );

    console.log(
        "=============================="
    );

    console.log(
        "WEBREF FUNCTIONS:",
        functions.length
    );

    console.log(
        "CREATED: webref-functions.json"
    );

    console.log(
        "=============================="
    );

    for (const fn of functions) {
        console.log(fn.name);
    }
}

main().catch(error => {
    console.error("ERROR:", error);
});
