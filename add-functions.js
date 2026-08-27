const fs = require("fs");

const file = "all-css.json";

const newFunctions = [
  "-webkit-image-set()",
  "abs()",
  "acos()",
  "alpha()",
  "anchor()",
  "anchor-size()",
  "asin()",
  "atan()",
  "atan2()",
  "attr()",
  "blur()",
  "brightness()",
  "calc()",
  "calc-interpolate()",
  "calc-mix()",
  "calc-size()",
  "circle()",
  "clamp()",
  "color()",
  "color-hdr()",
  "color-interpolate()",
  "color-layers()",
  "color-mix()",
  "conic-gradient()",
  "content()",
  "contrast()",
  "contrast-color()",
  "control-value()",
  "cos()",
  "counter()",
  "counters()",
  "cross-fade()",
  "cubic-bezier()",
  "device-cmyk()",
  "drop-shadow()",
  "dynamic-range-limit-mix()",
  "element()",
  "ellipse()",
  "env()",
  "exp()",
  "fade()",
  "filter()",
  "first-valid()",
  "fit-content()",
  "grayscale()",
  "hdr-color()",
  "hsl()",
  "hsla()",
  "hue-rotate()",
  "hwb()",
  "hypot()",
  "ictcp()",
  "ident()",
  "if()",
  "image()",
  "image-set()",
  "inherit()",
  "inset()",
  "integrity()",
  "interpolate()",
  "invert()",
  "jzazbz()",
  "jzczhz()",
  "lab()",
  "lch()",
  "leader()",
  "light-dark()",
  "linear()",
  "linear-gradient()",
  "log()",
  "matrix()",
  "matrix3d()",
  "max()",
  "media()",
  "min()",
  "minmax()",
  "mod()",
  "oklab()",
  "oklch()",
  "opacity()",
  "paint()",
  "palette-mix()",
  "param()",
  "path()",
  "perspective()",
  "pointer()",
  "polygon()",
  "pow()",
  "progress()",
  "radial-gradient()",
  "random()",
  "random-item()",
  "ray()",
  "rect()",
  "referrer-policy()",
  "rem()",
  "repeat()",
  "repeating-conic-gradient()",
  "repeating-linear-gradient()",
  "repeating-radial-gradient()",
  "rgb()",
  "rgba()",
  "rotate()",
  "rotate3d()",
  "rotateX()",
  "rotateY()",
  "rotateZ()",
  "round()",
  "saturate()",
  "scale()",
  "scale3d()",
  "scaleX()",
  "scaleY()",
  "scaleZ()",
  "scroll()",
  "scroll-button()",
  "scroll-state()",
  "sepia()",
  "shape()",
  "sibling-count()",
  "sibling-index()",
  "sign()",
  "sin()",
  "skew()",
  "skewX()",
  "skewY()",
  "snap-block()",
  "snap-inline()",
  "sqrt()",
  "src()",
  "steps()",
  "string()",
  "stripes()",
  "superellipse()",
  "supports()",
  "symbols()",
  "tan()",
  "target-counter()",
  "target-counters()",
  "target-text()",
  "toggle()",
  "transform-interpolate()",
  "transform-mix()",
  "translate()",
  "translate3d()",
  "translateX()",
  "translateY()",
  "translateZ()",
  "type()",
  "url()",
  "url-pattern()",
  "var()",
  "view()",
  "view-transition-group()",
  "view-transition-group-children()",
  "view-transition-image-pair()",
  "view-transition-new()",
  "view-transition-old()",
  "wcag2()",
  "xywh()"
];

const data = JSON.parse(fs.readFileSync(file, "utf8"));

if (!Array.isArray(data.functions)) {
  console.error("ERROR: data.functions is not an array");
  process.exit(1);
}

const before = data.functions.length;

const existing = new Set(
  data.functions.map(x => {
    if (typeof x === "string") {
      return x.toLowerCase();
    }

    if (x && typeof x === "object" && x.name) {
      return String(x.name).toLowerCase();
    }

    return "";
  })
);

let added = 0;

for (const fn of newFunctions) {

  const key = fn.toLowerCase();

  if (existing.has(key)) {
    continue;
  }

  data.functions.push({
    name: fn,
    href: "",
    syntax: "",
    for: []
  });

  existing.add(key);
  added++;
}

fs.writeFileSync(
  file,
  JSON.stringify(data, null, 2),
  "utf8"
);

console.log("================================");
console.log("FUNCTIONS BEFORE:", before);
console.log("ADDED:", added);
console.log("FUNCTIONS AFTER:", data.functions.length);
console.log("================================");
console.log("Saved:", file);
