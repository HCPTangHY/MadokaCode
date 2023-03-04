UNOWNED_ID = -1;
function getMap(ctx,stars) {
    let map = [];

    for (let i = 0; i < 400; i++) {
        let column = [];
        for (let j = 0; j < 400; j++) {
            let obj = {};
            obj['owner'] = -1;
            column.push(obj);
        }
        map.push(column);
    }
    for (let [id, star] of Object.entries(stars)) {
        let baseRadius = 10;

        let owner = star.owner;

        let imin = Math.max(0, Math.floor((star.x*3+960) / 4.8));
        let imax = Math.min(400, Math.floor((star.x*3+960) / 4.8));
        let jmin = Math.max(0, Math.floor((star.y*3+960) / 4.8));
        let jmax = Math.min(400, Math.floor((star.y*3+960) / 4.8));
        console.log(id,imin,imax,jmin,jmax);
        for (let i = imin; i <= imax; i++) {
            let column = map[i];
            for (let j = jmin; j <= jmax; j++) {

                let x = 4.8 * i;
                let y = 4.8 * j;

                let distance = Math.hypot(star.x - x, star.y - y);
                let value = baseRadius - distance;

                if (distance < 100) {
                    value -= baseRadius - 20;
                    value *= 100;
                    value += baseRadius - 20;
                }

                value = Math.max(0, value);

                // column[j][owner] += value;
                column[j]['owner'] = owner;
            }
        }
    }
    return map;
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

    let xmin = Infinity;
    let xmax = -Infinity;
    let ymin = Infinity;
    let ymax = -Infinity;

    let innerRadius = Infinity;
    let stars = data.star;
    console.log(getMap(ctx,stars));

}
