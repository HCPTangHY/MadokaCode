function changeOwner(id,owner) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ChangeOwner',{
        id : id,
        owner : owner,
    },function(color) {
        $("#MenuLink-"+id).css("background-color",color);
    })
}
function readStar(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ReadStar',{
        id : id,
    },function(data) {
        data = JSON.parse(data);
        console.log(data);
        $('#starName').empty();
        $('#starName').html(data['star']['name']);
        $('#starOwner').empty()
        $('#starOwner').html("<img src=\"storage/img/countries/"+data['star']['owner']+".png\" width=\"20px\">")
        $('#starController').empty();
        $('#starController').html("<img src=\"storage/img/countries/"+data['star']['controller']+".png\" width=\"35px\">");
        $('#station').empty();
        if (data['station']!=0) {
            $('#station').html("<div class=\"row\">\n" +
                "                        <div class=\"col-1 my-4 py-4\">\n" +
                "                            <div class=\"container my-4\">\n" +
                "                                <h5 class=\"text-center\" id=\"stationLevel\">"+data['station']['type']+"</h5>\n" +
                "                            </div>\n" +
                "                        </div>\n" +
                "                        <div class=\"col-3 container my-4 py-4 rounded shadow-lg\">\n" +
                "\n" +
                "                        </div>\n" +
                "                        <div class=\"col-3 container my-4 py-4 rounded shadow-lg\">\n" +
                "\n" +
                "                        </div>\n" +
                "                    </div>");
        }
        $('#resource').empty();
        for (var key in data['star']['resource']) {
            if (data['star']['resource'][key]!= 0) {
                $('#resource').append("<span className=\"badge bg-light text-dark\" style=\"display: inline\"><img src=\"storage/img/resource/"+key+".png\" width=\"20px\">"+data['star']['resource'][key]+"</span>");
            }
        }

        const starModal = new bootstrap.Modal("#starModal");
        starModal.show();
    });
}
