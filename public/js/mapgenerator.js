RES_X = 384 + 1;
RES_Y = 384 + 1;
ENTRIES_KEY = '___entries';
UNOWNED_ID = -1000000000;
MISSING_COLOR = 'rgb(255,255,255)';
BORDERS = [
    {color: 'rgba(225,225,225,0.4)', width: 10, light: false, regular: true},
    {color: 'rgb(235,235,235)', width: 2, light: true, regular: true}
];
EDGE_BORDERS = [
    {color: 'rgba(5,5,5,0.5)', width: 14, light: false, regular: true},
    {color: 'rgba(5,5,5,0.5)', width: 7, light: true, regular: false}
];

MAP_WIDTH = 1920;
MAP_HEIGHT = 1920;

UNOWNED_VALUE = 20;
UNOWNED_RADIUS = 110;
UNOWNED_HYPERLANE_RADIUS = 95;
SYSTEM_RADIUS = UNOWNED_RADIUS + UNOWNED_VALUE - 6;
HYPERLANE_RADIUS = UNOWNED_HYPERLANE_RADIUS + UNOWNED_VALUE - 6;

PADDING = 50;
INNER_PADDING = 10;
PAINT_SCALE_FACTOR = 200;
NEAR_RADIUS = 20;
NEAR_BOOST = 100;
function lerp(p0, p1, a0, b0, a1, b1) {

    a0 = a0 || 0;
    b0 = b0 || 0;
    a1 = a1 || 0;
    b1 = b1 || 0;

    a0 -= b0;
    a1 -= b1;

    if (a0 == a1) return 0.5 * p0 + 0.5 * p1;

    let t = (0 - a0) / (a1 - a0);
    if (!Number.isFinite(t)) t = 0.5;

    return (1 - t) * p0 + t * p1;

}
function getMap(stars, hyperlanes, scaleFactor, sizeX, sizeY) {

    let map = [];

    for (let i = 0; i < RES_X; i++) {
        let column = [];
        for (let j = 0; j < RES_Y; j++) {

            let obj = {};
            obj[UNOWNED_ID] = scaleFactor * UNOWNED_VALUE;
            column.push(obj);

        }
        map.push(column);
    }

    for (let [id, star] of Object.entries(stars)) {

        let baseRadius = SYSTEM_RADIUS;

        let owner = star.owner;
        if (owner == null) {
            owner = UNOWNED_ID;
            baseRadius = UNOWNED_RADIUS;
        } else {
            owner = +owner;
        }

        baseRadius *= scaleFactor;

        let imin = Math.max(0, Math.floor((star.x - baseRadius) / sizeX));
        let imax = Math.min(RES_X, Math.floor((star.x + baseRadius) / sizeX));
        let jmin = Math.max(0, Math.floor((star.y - baseRadius) / sizeY));
        let jmax = Math.min(RES_Y, Math.floor((star.y + baseRadius) / sizeY));

        for (let i = imin; i < imax; i++) {
            column = map[i];
            for (let j = jmin; j < jmax; j++) {

                let x = sizeX * i;
                let y = sizeY * j;

                let distance = Math.hypot(star.x - x, star.y - y);
                let value = baseRadius - distance;

                if (distance < NEAR_RADIUS * scaleFactor) {
                    value -= baseRadius - NEAR_RADIUS * scaleFactor;
                    value *= NEAR_BOOST;
                    value += baseRadius - NEAR_RADIUS * scaleFactor;
                }

                value = Math.max(0, value);

                if (column[j][owner] == null) column[j][owner] = 0;
                // column[j][owner] += value;
                column[j][owner] = Math.max(column[j][owner], value);

            }
        }

    }

    for (let [key, hyperlane] of Object.entries(hyperlanes)) {

        if (stars[hyperlane.from].owner != stars[hyperlane.to].owner) continue;

        let baseRadius = HYPERLANE_RADIUS;

        let owner = stars[hyperlane.from].owner;
        if (owner == null) {
            owner = UNOWNED_ID;
            baseRadius = UNOWNED_HYPERLANE_RADIUS;
        } else {
            owner = +owner;
        }

        baseRadius *= scaleFactor;

        let x1 = stars[hyperlane.from].x;
        let y1 = stars[hyperlane.from].y;
        let x2 = stars[hyperlane.to].x;
        let y2 = stars[hyperlane.to].y;

        // ax + by = c

        let a = y1 - y2;
        let b = x2 - x1;
        let c = a * x1 + b * y1;

        let x3 = x1 - (y2 - y1);
        let y3 = y1 + (x2 - x1);

        let distanceFactor = Math.hypot(x3 - x1, y3 - y1) / Math.abs(a * x3 + b * y3 - c);

        // normal

        let a2 = b;
        let b2 = -a;
        let c2 = a2 * ((x1 + x2) / 2) + b2 * ((y1 + y2) / 2);

        // let normalFactor = Math.hypot((x2 - x1) / 2, (y2 - y1) / 2) / Math.abs(a2 * x2 + b2 * y2 - c2);
        let halfLength = Math.abs(a2 * x1 + b2 * y1 - c2);

        let imin = Math.max(0, Math.floor((Math.min(x1, x2) - baseRadius) / sizeX));
        let imax = Math.min(RES_X, Math.floor((Math.max(x1, x2) + baseRadius) / sizeX));
        let jmin = Math.max(0, Math.floor((Math.min(y1, y2) - baseRadius) / sizeY));
        let jmax = Math.min(RES_Y, Math.floor((Math.max(y1, y2) + baseRadius) / sizeY));

        for (let i = imin; i < imax; i++) {
            column = map[i];
            for (let j = jmin; j < jmax; j++) {

                let x = sizeX * i;
                let y = sizeY * j;

                let distance = Math.min(Math.hypot(x - x1, y - y1), Math.hypot(x - x2, y - y2));

                if (Math.abs(a2 * x + b2 * y - c2) <= halfLength) {
                    distance = Math.min(distance, distanceFactor * Math.abs(a * x + b * y - c));
                }

                let value = baseRadius - distance;

                if (distance < NEAR_RADIUS / 2  * scaleFactor) {
                    value -= baseRadius - NEAR_RADIUS / 2 * scaleFactor;
                    value *= NEAR_BOOST / 2;
                    value += baseRadius - NEAR_RADIUS / 2 * scaleFactor;
                }

                value = Math.max(0, value);

                if (!Number.isFinite(value)) continue;

                if (column[j][owner] == null) column[j][owner] = 0;
                // column[j][owner] += value;
                column[j][owner] = Math.max(column[j][owner], value);

            }
        }

    }

    return map;

}
function drawCountryFill(ctx, data, map, values, sizeX, sizeY) {

    ctx.globalCompositeOperation = 'source-over';
    ctx.globalAlpha = 1.0;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeWidth = 1.0;

    for (let [tag,color] of Object.entries(data.country)) {

        if (tag == UNOWNED_ID) continue;

        ctx.fillStyle = color || MISSING_COLOR;
        ctx.strokeStyle = color || MISSING_COLOR;

        ctx.beginPath();

        for (let j = 0; j + 1 < RES_Y; j++) {

            for (let i = 0; i + 1 < RES_X; i++) {

                let value_UL = values[i][j];
                let value_UR = values[i + 1][j];
                let value_BL = values[i][j + 1];
                let value_BR = values[i + 1][j + 1];

                if (value_UL.id != tag && value_UR.id != tag && value_BL.id != tag && value_BR.id != tag) continue;

                let xmin = sizeX * i;
                let xmax = xmin + sizeX;
                let ymin = sizeY * j;
                let ymax = ymin + sizeY;

                let UL = map[i][j];
                let UR = map[i + 1][j];
                let BL = map[i][j + 1];
                let BR = map[i + 1][j + 1];

                if (value_UL.id == value_UR.id && value_UR.id == value_BL.id && value_BL.id == value_BR.id) {
                    if (value_UL.id != tag) continue; // for some reason this breaks if I use a triple equals

                    ctx.rect(xmin, ymin, sizeX, sizeY);

                    continue;
                }

                if (value_UL.id == value_BR.id && value_UR.id == value_BL.id) {

                    let a = value_UL.id;
                    let b = value_UR.id;

                    let mida = 0;
                    let midb = 0;

                    if ((UR[a] || 0) == 0 || (BL[a] || 0) == 0) {
                        mida = 0.5 * value_UL.value + 0.5 * value_BR.value;
                    } else {
                        mida = 0.5 * (0.5 * value_UL.value + 0.5 * UR[a]) + 0.5 * (0.5 * value_BR.value + 0.5 * BL[a]);
                    }

                    if ((UL[b] || 0) == 0 || (BR[b] || 0) == 0) {
                        midb = 0.5 * value_UR.value + 0.5 * value_BL.value;
                    } else {
                        midb = 0.5 * (0.5 * value_UR.value + 0.5 * UL[b]) + 0.5 * (0.5 * value_BL.value + 0.5 * BR[b]);
                    }

                    let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                    let bottomMid = lerp(xmin, xmax, (BL[a] || 0), value_BL.value, value_BR.value, (BR[b] || 0));

                    let leftMid = lerp(ymin, ymax, value_UL.value, (UL[b] || 0), (BL[a] || 0), value_BL.value);
                    let rightMid = lerp(ymin, ymax, (UR[a] || 0), value_UR.value, value_BR.value, (BR[b] || 0));

                    if (midb > mida) {

                        if (value_UL.id == tag) {
                            ctx.moveTo(xmin, ymin);
                            ctx.lineTo(topMid, ymin);
                            ctx.lineTo(xmin, leftMid);
                            ctx.closePath();

                            ctx.moveTo(xmax, ymax);
                            ctx.lineTo(bottomMid, ymax);
                            ctx.lineTo(xmax, rightMid);
                            ctx.closePath();
                        }

                        if (value_UR.id == tag) {
                            ctx.moveTo(xmax, ymin);
                            ctx.lineTo(xmax, rightMid);
                            ctx.lineTo(bottomMid, ymax);
                            ctx.lineTo(xmin, ymax);
                            ctx.lineTo(xmin, leftMid);
                            ctx.lineTo(topMid, ymin);
                        }

                    } else {

                        if (value_UL.id == tag) {
                            ctx.moveTo(xmin, ymin);
                            ctx.lineTo(topMid, ymin);
                            ctx.lineTo(xmax, rightMid);
                            ctx.lineTo(xmax, ymax);
                            ctx.lineTo(bottomMid, ymax);
                            ctx.lineTo(xmin, leftMid);
                            ctx.closePath();
                        }

                        if (value_UR.id == tag) {
                            ctx.moveTo(xmax, ymin);
                            ctx.lineTo(xmax, rightMid);
                            ctx.lineTo(topMid, ymin);
                            ctx.closePath();

                            ctx.moveTo(xmin, ymax);
                            ctx.lineTo(xmin, leftMid);
                            ctx.lineTo(bottomMid, ymax);
                            ctx.closePath();
                        }

                    }

                    continue;

                }

                let a = value_UL.id;
                let b = value_UR.id;
                let c = value_BR.id;
                let d = value_BL.id;

                let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                let bottomMid = lerp(xmax, xmin, value_BR.value, (BR[d] || 0), (BL[c] || 0), value_BL.value);

                let rightMid = lerp(ymin, ymax, value_UR.value, (UR[c] || 0), (BR[b] || 0), value_BR.value);
                let leftMid = lerp(ymax, ymin, value_BL.value, (BL[a] || 0), (UL[d] || 0), value_UL.value);

                if (a == tag) {

                    ctx.moveTo(xmin, ymin);
                    ctx.lineTo(topMid, ymin);

                    if (a == b) {
                        b = UNOWNED_ID;
                        ctx.lineTo(topMid, ymin);
                        ctx.lineTo(xmax, ymin);
                        ctx.lineTo(xmax, rightMid);
                    }

                    if (a == c) {
                        c = UNOWNED_ID;
                        ctx.lineTo(xmax, rightMid);
                        ctx.lineTo(xmax, ymax);
                        ctx.lineTo(bottomMid, ymax);
                    }

                    if (a == d) {
                        d = UNOWNED_ID;
                        ctx.lineTo(bottomMid, ymax);
                        ctx.lineTo(xmin, ymax);
                        ctx.lineTo(xmin, leftMid);
                    }

                    ctx.lineTo(xmin, leftMid);

                    ctx.closePath();

                }

                if (b == tag) {

                    ctx.moveTo(xmax, ymin);
                    ctx.lineTo(xmax, rightMid);

                    if (b == c) {
                        c = UNOWNED_ID;
                        ctx.lineTo(xmax, rightMid);
                        ctx.lineTo(xmax, ymax);
                        ctx.lineTo(bottomMid, ymax);
                    }

                    if (b == d) {
                        d = UNOWNED_ID;
                        ctx.lineTo(bottomMid, ymax);
                        ctx.lineTo(xmin, ymax);
                        ctx.lineTo(xmin, leftMid);
                    }

                    ctx.lineTo(topMid, ymin);

                    ctx.closePath();

                }

                if (c == tag) {

                    ctx.moveTo(xmax, ymax);
                    ctx.lineTo(bottomMid, ymax);

                    if (c == d) {
                        d = UNOWNED_ID;
                        ctx.lineTo(bottomMid, ymax);
                        ctx.lineTo(xmin, ymax);
                        ctx.lineTo(xmin, leftMid);
                    }

                    ctx.lineTo(xmax, rightMid);

                    ctx.closePath();

                }

                if (d == tag) {

                    ctx.moveTo(xmin, ymax);
                    ctx.lineTo(xmin, leftMid);
                    ctx.lineTo(bottomMid, ymax);

                    ctx.closePath();

                }

            }

        }

        ctx.fill();
        ctx.stroke();

    }

}
function drawEdgeBorders(ctx, map, values, sizeX, sizeY) {

    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalCompositeOperation = 'source-atop';
    ctx.globalAlpha = 1.0;

    for (let border of EDGE_BORDERS) {

        ctx.strokeStyle = border.color;
        ctx.lineWidth = border.width;
        ctx.beginPath();

        for (let j = 0; j + 1 < RES_Y; j++) {

            for (let i = 0; i + 1 < RES_X; i++) {

                let value_UL = values[i][j];
                let value_UR = values[i + 1][j];
                let value_BL = values[i][j + 1];
                let value_BR = values[i + 1][j + 1];

                let xmin = sizeX * i;
                let xmax = xmin + sizeX;
                let ymin = sizeY * j;
                let ymax = ymin + sizeY;

                let UL = map[i][j];
                let UR = map[i + 1][j];
                let BL = map[i][j + 1];
                let BR = map[i + 1][j + 1];

                if (value_UL.id == value_UR.id && value_UR.id == value_BL.id && value_BL.id == value_BR.id) {
                    continue;
                }

                if (value_UL.id == value_BR.id && value_UR.id == value_BL.id) {

                    let a = value_UL.id;
                    let b = value_UR.id;

                    if (a == null) a == UNOWNED_ID;
                    if (b == null) b == UNOWNED_ID;

                    if (a != UNOWNED_ID && b != UNOWNED_ID) continue;

                    let mida = 0;
                    let midb = 0;

                    if ((UR[a] || 0) == 0 || (BL[a] || 0) == 0) {
                        mida = 0.5 * value_UL.value + 0.5 * value_BR.value;
                    } else {
                        mida = 0.5 * (0.5 * value_UL.value + 0.5 * UR[a]) + 0.5 * (0.5 * value_BR.value + 0.5 * BL[a]);
                    }

                    if ((UL[b] || 0) == 0 || (BR[b] || 0) == 0) {
                        midb = 0.5 * value_UR.value + 0.5 * value_BL.value;
                    } else {
                        midb = 0.5 * (0.5 * value_UR.value + 0.5 * UL[b]) + 0.5 * (0.5 * value_BL.value + 0.5 * BR[b]);
                    }

                    let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                    let bottomMid = lerp(xmin, xmax, (BL[a] || 0), value_BL.value, value_BR.value, (BR[b] || 0));

                    let leftMid = lerp(ymin, ymax, value_UL.value, (UL[b] || 0), (BL[a] || 0), value_BL.value);
                    let rightMid = lerp(ymin, ymax, (UR[a] || 0), value_UR.value, value_BR.value, (BR[b] || 0));

                    if (midb > mida) {

                        ctx.moveTo(topMid, ymin);
                        ctx.lineTo(xmin, leftMid);

                        ctx.moveTo(xmax, rightMid);
                        ctx.lineTo(bottomMid, ymax);

                    } else {

                        ctx.moveTo(topMid, ymin);
                        ctx.lineTo(xmax, rightMid);

                        ctx.moveTo(xmin, leftMid);
                        ctx.lineTo(bottomMid, ymax);

                    }

                    continue;

                }

                let a = value_UL.id;
                let b = value_UR.id;
                let c = value_BR.id;
                let d = value_BL.id;

                if (a == null) a == UNOWNED_ID;
                if (b == null) b == UNOWNED_ID;
                if (c == null) c == UNOWNED_ID;
                if (d == null) d == UNOWNED_ID;

                let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                let bottomMid = lerp(xmax, xmin, value_BR.value, (BR[d] || 0), (BL[c] || 0), value_BL.value);

                let rightMid = lerp(ymin, ymax, value_UR.value, (UR[c] || 0), (BR[b] || 0), value_BR.value);
                let leftMid = lerp(ymax, ymin, value_BL.value, (BL[a] || 0), (UL[d] || 0), value_UL.value);

                if (a != d && a != b && (a == UNOWNED_ID || d == UNOWNED_ID || b == UNOWNED_ID)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(topMid, ymin);
                }

                if (b != a && b != c && (b == UNOWNED_ID || a == UNOWNED_ID || c == UNOWNED_ID)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(xmax, rightMid);
                }

                if (c != b && c != d && (c == UNOWNED_ID || b == UNOWNED_ID || d == UNOWNED_ID)) {
                    ctx.moveTo(xmax, rightMid);
                    ctx.lineTo(bottomMid, ymax);
                }

                if (d != c && d != a && (d == UNOWNED_ID || c == UNOWNED_ID || a == UNOWNED_ID)) {
                    ctx.moveTo(bottomMid, ymax);
                    ctx.lineTo(xmin, leftMid);
                }

                if ((a == b) && !(a == d || b == c) && (d == UNOWNED_ID || c == UNOWNED_ID)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(xmax, rightMid);
                } else if ((c == d) && !(c == b || d == a) && (b == UNOWNED_ID || a == UNOWNED_ID)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(xmax, rightMid);
                }

                if ((b == c) && !(b == a || c == d) && (a == UNOWNED_ID || d == UNOWNED_ID)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(bottomMid, ymax);
                } else if ((d == a) && !(d == c || a == b) && (c == UNOWNED_ID || b == UNOWNED_ID)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(bottomMid, ymax);
                }

            }

        }
        ctx.stroke();
    }

}
function drawCountryBorders(ctx, map, values, sizeX, sizeY) {

    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalCompositeOperation = 'source-over';
    ctx.globalAlpha = 1.0;

    for (let border of BORDERS) {

        ctx.strokeStyle = border.color;
        ctx.lineWidth = border.width;
        ctx.beginPath();

        for (let j = 0; j + 1 < RES_Y; j++) {

            for (let i = 0; i + 1 < RES_X; i++) {

                let value_UL = values[i][j];
                let value_UR = values[i + 1][j];
                let value_BL = values[i][j + 1];
                let value_BR = values[i + 1][j + 1];

                let xmin = sizeX * i;
                let xmax = xmin + sizeX;
                let ymin = sizeY * j;
                let ymax = ymin + sizeY;

                let UL = map[i][j];
                let UR = map[i + 1][j];
                let BL = map[i][j + 1];
                let BR = map[i + 1][j + 1];

                if (value_UL.id == value_UR.id && value_UR.id == value_BL.id && value_BL.id == value_BR.id) {
                    continue;
                }

                if (value_UL.id == value_BR.id && value_UR.id == value_BL.id) {

                    let a = value_UL.id;
                    let b = value_UR.id;

                    if (a == null) a == UNOWNED_ID;
                    if (b == null) b == UNOWNED_ID;

                    if (a == UNOWNED_ID || b == UNOWNED_ID) continue;

                    let mida = 0;
                    let midb = 0;

                    if ((UR[a] || 0) == 0 || (BL[a] || 0) == 0) {
                        mida = 0.5 * value_UL.value + 0.5 * value_BR.value;
                    } else {
                        mida = 0.5 * (0.5 * value_UL.value + 0.5 * UR[a]) + 0.5 * (0.5 * value_BR.value + 0.5 * BL[a]);
                    }

                    if ((UL[b] || 0) == 0 || (BR[b] || 0) == 0) {
                        midb = 0.5 * value_UR.value + 0.5 * value_BL.value;
                    } else {
                        midb = 0.5 * (0.5 * value_UR.value + 0.5 * UL[b]) + 0.5 * (0.5 * value_BL.value + 0.5 * BR[b]);
                    }

                    let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                    let bottomMid = lerp(xmin, xmax, (BL[a] || 0), value_BL.value, value_BR.value, (BR[b] || 0));

                    let leftMid = lerp(ymin, ymax, value_UL.value, (UL[b] || 0), (BL[a] || 0), value_BL.value);
                    let rightMid = lerp(ymin, ymax, (UR[a] || 0), value_UR.value, value_BR.value, (BR[b] || 0));

                    if (midb > mida) {

                        ctx.moveTo(topMid, ymin);
                        ctx.lineTo(xmin, leftMid);

                        ctx.moveTo(xmax, rightMid);
                        ctx.lineTo(bottomMid, ymax);

                    } else {

                        ctx.moveTo(topMid, ymin);
                        ctx.lineTo(xmax, rightMid);

                        ctx.moveTo(xmin, leftMid);
                        ctx.lineTo(bottomMid, ymax);

                    }

                    continue;

                }

                let a = value_UL.id;
                let b = value_UR.id;
                let c = value_BR.id;
                let d = value_BL.id;

                if (a == null) a == UNOWNED_ID;
                if (b == null) b == UNOWNED_ID;
                if (c == null) c == UNOWNED_ID;
                if (d == null) d == UNOWNED_ID;

                let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
                let bottomMid = lerp(xmax, xmin, value_BR.value, (BR[d] || 0), (BL[c] || 0), value_BL.value);

                let rightMid = lerp(ymin, ymax, value_UR.value, (UR[c] || 0), (BR[b] || 0), value_BR.value);
                let leftMid = lerp(ymax, ymin, value_BL.value, (BL[a] || 0), (UL[d] || 0), value_UL.value);

                if (a != d && a != b && !(a == UNOWNED_ID || d == UNOWNED_ID || b == UNOWNED_ID)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(topMid, ymin);
                }

                if (b != a && b != c && !(b == UNOWNED_ID || a == UNOWNED_ID || c == UNOWNED_ID)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(xmax, rightMid);
                }

                if (c != b && c != d && !(c == UNOWNED_ID || b == UNOWNED_ID || d == UNOWNED_ID)) {
                    ctx.moveTo(xmax, rightMid);
                    ctx.lineTo(bottomMid, ymax);
                }

                if (d != c && d != a && !(d == UNOWNED_ID || c == UNOWNED_ID || a == UNOWNED_ID)) {
                    ctx.moveTo(bottomMid, ymax);
                    ctx.lineTo(xmin, leftMid);
                }

                if (a == UNOWNED_ID || b == UNOWNED_ID || c == UNOWNED_ID || d == UNOWNED_ID) continue;

                if ((a == b) && !(a == d || b == c)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(xmax, rightMid);
                } else if ((c == d) && !(c == b || d == a)) {
                    ctx.moveTo(xmin, leftMid);
                    ctx.lineTo(xmax, rightMid);
                }

                if ((b == c) && !(b == a || c == d)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(bottomMid, ymax);
                } else if ((d == a) && !(d == c || a == b)) {
                    ctx.moveTo(topMid, ymin);
                    ctx.lineTo(bottomMid, ymax);
                }

            }

        }

        ctx.stroke();

    }

    ctx.fillStyle = BORDERS[BORDERS.length - 1].color;
    ctx.beginPath();

    let points = [[0, 0], [0, 0], [0, 0], [0, 0]];

    for (let j = 0; j + 1 < RES_Y; j++) {

        for (let i = 0; i + 1 < RES_X; i++) {

            let value_UL = values[i][j];
            let value_UR = values[i + 1][j];
            let value_BL = values[i][j + 1];
            let value_BR = values[i + 1][j + 1];

            let xmin = sizeX * i;
            let xmax = xmin + sizeX;
            let ymin = sizeY * j;
            let ymax = ymin + sizeY;

            let UL = map[i][j];
            let UR = map[i + 1][j];
            let BL = map[i][j + 1];
            let BR = map[i + 1][j + 1];

            if (value_UL.id == value_UR.id && value_UR.id == value_BL.id && value_BL.id == value_BR.id) {
                continue;
            }

            if (value_UL.id == value_BR.id && value_UR.id == value_BL.id) {
                continue;
            }

            let a = value_UL.id;
            let b = value_UR.id;
            let c = value_BR.id;
            let d = value_BL.id;

            if (a == null) a == UNOWNED_ID;
            if (b == null) b == UNOWNED_ID;
            if (c == null) c == UNOWNED_ID;
            if (d == null) d == UNOWNED_ID;

            let topMid = lerp(xmin, xmax, value_UL.value, (UL[b] || 0), (UR[a] || 0), value_UR.value);
            let bottomMid = lerp(xmax, xmin, value_BR.value, (BR[d] || 0), (BL[c] || 0), value_BL.value);

            let rightMid = lerp(ymin, ymax, value_UR.value, (UR[c] || 0), (BR[b] || 0), value_BR.value);
            let leftMid = lerp(ymax, ymin, value_BL.value, (BL[a] || 0), (UL[d] || 0), value_UL.value);

            let numPoints = 0;

            if ((a != b) && !(a == UNOWNED_ID || b == UNOWNED_ID)) {
                points[numPoints][0] = topMid;
                points[numPoints++][1] = ymin;
            }

            if ((b != c) && !(b == UNOWNED_ID || c == UNOWNED_ID)) {
                points[numPoints][0] = xmax;
                points[numPoints++][1] = rightMid;
            }

            if ((c != d) && !(c == UNOWNED_ID || d == UNOWNED_ID)) {
                points[numPoints][0] = bottomMid;
                points[numPoints++][1] = ymax;
            }

            if ((d != a) && !(d == UNOWNED_ID || a == UNOWNED_ID)) {
                points[numPoints][0] = xmin;
                points[numPoints++][1] = leftMid;
            }

            if (numPoints < 3) continue;

            ctx.moveTo(points[0][0], points[0][1]);
            for (let i = 1; i < numPoints; i++) {
                ctx.lineTo(points[i][0], points[i][1]);
            }
            ctx.closePath();

        }

    }

    ctx.fill();

}
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$.post('/Action/MapData',{
},function(data) {
    data = JSON.parse(data);
    generateMap(data);
});
function generateMap(data) {
    let mapMode = 'normal';
    let colorVariant = 'map';

    const canvas = document.getElementById('galaxyMap');
    const ctx = canvas.getContext('2d');

    // Get map data

    let stars = {};
    let hyperlanes = {};

    let xmin = Infinity;
    let xmax = -Infinity;
    let ymin = Infinity;
    let ymax = -Infinity;

    let innerRadius = Infinity;

    for (let [id, star] of Object.entries(data.star)) {

        id = Math.round(+id);

        xmin = Math.min(xmin, +star.x);
        xmax = Math.max(xmax, +star.x);
        ymin = Math.min(ymin, +star.y);
        ymax = Math.max(ymax, +star.y);

        innerRadius = Math.min(innerRadius, Math.hypot(+star.x, +star.y));

        stars[id] = {
            x: +star.x,
            y: +star.y,
            owner: star.owner,
            hyperlanes: !(star.hyperlane == null || star.hyperlane.length === 0),
            capital: false,
            id: id
        };

        if (star.hyperlane != null) {
            for (let hyperlane of star.hyperlane) {
                let from = id;
                let to = Math.round(+hyperlane.to);

                if (from > to) {
                    let tmp = from;
                    from = to;
                    to = tmp;
                }

                let key = `${from},${to}`;

                hyperlanes[key] = {
                    from: from,
                    to: to
                };
            }
        }
    }
    // Generate map

    const sizeX = MAP_WIDTH / (RES_X - 1);
    const sizeY = MAP_HEIGHT / (RES_Y - 1);

    let scale = Math.min(
        (MAP_WIDTH / 2 - (SYSTEM_RADIUS - UNOWNED_VALUE) - PADDING) / Math.max(Math.abs(xmax), Math.abs(xmin)),
        (MAP_HEIGHT / 2 - (SYSTEM_RADIUS - UNOWNED_VALUE) - PADDING) / Math.max(Math.abs(ymax), Math.abs(ymin))
    );

    innerRadius = Math.max(0, Math.min(MAP_WIDTH, MAP_HEIGHT, scale * innerRadius - INNER_PADDING));

    let scaleFactor = scale / PAINT_SCALE_FACTOR;

    let ax = MAP_WIDTH / 2;
    let ay = MAP_HEIGHT / 2;

    for (let [id, star] of Object.entries(stars)) {
        star.x = -scale * star.x + ax;
        star.y = scale * star.y + ay
    }

    let map = getMap(stars, hyperlanes, scaleFactor, sizeX, sizeY);

    let values = [];
    for (let i = 0; i < RES_X; i++) {
        let column = [];
        for (let j = 0; j < RES_Y; j++) {

            let value = {id: UNOWNED_ID, value: 0};
            for (let [id, val] of Object.entries(map[i][j])) {
                if (val > value.value) {
                    value.id = id;
                    value.value = val;
                }
            }

            column.push(value);
        }
        values.push(column);
    }

    // Draw map
    canvas.width = MAP_WIDTH;
    canvas.height = MAP_HEIGHT;
    // canvas.style.width = 'min(200vw, 1024px)';
    // canvas.style.height = 'min(200vw, 1024px)';
    ctx.filter = 'none';

    ctx.fillStyle = 'rgba(0,0,0,0)';
    ctx.globalCompositeOperation = 'copy';
    ctx.beginPath();
    ctx.rect(0, 0, MAP_WIDTH, MAP_HEIGHT);
    ctx.fill();

    drawCountryFill(ctx, colors, map, values, sizeX, sizeY);
    drawEdgeBorders(ctx, map, values, sizeX, sizeY);
    drawCountryBorders(ctx, map, values, sizeX, sizeY);

}
