<!DOCTYPE html>
<html lang="zh">
<head>
    <title>星图 - Madoka</title>
    <style type="text/css">
        html, body {
            margin: 0px;
        }
        canvas {
            /*background-image: url('storage/img/background.png');*/
            background-color: #000000;
        }
    </style>
    @include('components.header')
    <script type="application/javascript" src="{{asset('js/map.js')}}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container">
    @for($i=0; $i < count($stars); $i++)
        @php
            $ownerColor = '#ffffff';
            $ownerName = '';
            $controllerColor = '#ffffff';
            $controllerName = '';
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
            $country = $stars[$i]['owner'];
            $controller = $stars[$i]['controller'];
            $type = $stars[$i]['type'];
        @endphp
        @foreach($countrys as $key => $value)
            @if($value['tag'] == $country)
                @php
                    $ownerColor = $value['color'];
					$ownerName = $value['name'];
                @endphp
            @endif
        @endforeach
        @foreach($countrys as $key => $value)
            @if($value['tag'] == $controller)
                @php
                    $controllerColor = $value['color'];
                    $controllerName = $value['name'];
                @endphp
            @endif
        @endforeach
        @if ($country == '')
            @php
                $ownerColor = '#ffffff';
				$ownerName = '';
                $controllerColor = '#ffffff';
                $controllerName = '';
            @endphp
        @endif
        @if ($privilege == 0 || $privilege == 1)
{{--            @if($stars[$i]['havePlanet'] == 1)--}}
{{--                <button type='button' class='btn btn-default dropdown-toggle'--}}
{{--                        style='position: absolute;--}}
{{--                                top: {{$y-20}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;--}}
{{--                                border-radius: 100%;--}}
{{--                                background-color:hsla(0,0%,0%,0.00);--}}
{{--                                border:none ;--}}
{{--                                padding:0px 0px'--}}
{{--                        id='PlanetMenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'--}}
{{--                        data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>--}}
{{--                    @foreach($planets as $planet)--}}
{{--                        @php--}}
{{--                            if ($stars[$i]['id'] == $planet['position']) {--}}
{{--                                $img = $planet['type'];--}}
{{--                            }--}}
{{--                        @endphp--}}
{{--                    @endforeach--}}
{{--                    <img src='storage/img/planets/{{$img}}.png' width='20px'>--}}
{{--                </button>--}}
{{--                <ul class='dropdown-menu' aria-labelledby='PlanerMenuLink-{{$stars[$i]['id']}}' id='star-Planet-{{$stars[$i]['id']}}'>--}}
{{--                    <li><a class='dropdown-item' onclick='newPlanet({{$stars[$i]['id']}},"")'>无</a></li>--}}
{{--                    @for ($j=0; $j < count($planetTypes); $j++)--}}
{{--                        <li><a class='dropdown-item'--}}
{{--                               onclick='newPlanet({{$stars[$i]['id']}},"{{$planetTypes[$j]['name']}}")'>{{$planetTypes[$j]['localization']}}</a>--}}
{{--                        </li>--}}
{{--                    @endfor--}}
{{--                </ul>--}}
{{--            @endif--}}
            <button type='button' class='btn btn-default'
                    style='position: absolute;
                            top: {{$y-13.75}}px; left: {{$x-13.75}}px; width: 27.5px;height: 27.5px;
                            border-radius: 100%;
                            background-color:{{$controllerColor}};
                            border:none ;
                            padding:0px 0px'>
            </button>
            <button type='button' class='btn btn-default dropdown-toggle'
                    style='position: absolute;
                            top: {{$y-11}}px; left: {{$x-11}}px; width: 22px;height: 22px;
                            border-radius: 100%;
                            background-color:{{$ownerColor}};
                            border:none ;
                            padding:0px 0px'
                    id='MenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'
                    data-bs-target='#star-{{$stars[$i]['id']}}'>
                @if($type == 'sc_black_hole' || $type == 'sc_pulsar' || $type == 'sc_neutron_star')
                    <img style='position: relative; top: -2.5px; left: -2.5px;' src='{{asset("storage/img/".$type.".png")}}' width='27.5px' />
                @endif
                @if($stars[$i]['havePlanet'] == 1)
                    @php
                        $ownered = False;
                        foreach ($planets as $planet) {
                            if ($stars[$i]['id'] == $planet['position'] && $planet['controller'] != '') {
                                $ownered = true;
                                $countryImg = $planet['controller'];
                                break;
                            }
                        }
                    @endphp
                    @if ($ownered)
                        <img style='position: relative; top: -2.5px; left: -2.5px;' src='storage/img/countries/{{$countryImg}}.png' width='27.5px' />
                    @endif
                @endif
            </button>
            <ul class='dropdown-menu' aria-labelledby='MenuLink-{{$stars[$i]['id']}}' id='star-{{$stars[$i]['id']}}'>
                <li><a class='dropdown-item' onclick='changeOwner({{$stars[$i]['id']}},"")'>无</a></li>
                @for ($j=0; $j < count($countrys); $j++)
                    <li><a class='dropdown-item'
                           onclick='changeOwner({{$stars[$i]['id']}},"{{$countrys[$j]['tag']}}")'>{{$countrys[$j]['name']}}</a>
                    </li>
                @endfor
            </ul>
        @else
            @if($stars[$i]['havePlanet'] == 1)
                @php
                    $ownered = False;
                    foreach($planets as $planet) {
                        if ($stars[$i]['id'] == $planet['position']) {
                            $img = $planet['type'];
                            if ($planet['controller'] != '') {
                                $ownered = true;
                            }
                            break;
                        }
                    }
                @endphp
                @if (!$ownered)
                    <button type='button' class='btn btn-default'
                            style='position: absolute;
                            top: {{$y-20}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;
                            border-radius: 100%;
                            background-color:hsla(0,0%,0%,0.00);
                            border:none ;
                            padding:0px 0px'
                            id='Planet-{{$stars[$i]['id']}}' aria-expanded='false'
                            data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>
                        <img src='storage/img/planets/{{$img}}.png' width='20px' onclick="colonize({{$planet['id']}})"/>
                    </button>
                @endif
            @endif
            <button type='button' class='btn btn-default'
                    style='position: absolute;
                            top: {{$y-13.75}}px; left: {{$x-13.75}}px; width: 27.5px;height: 27.5px;
                            border-radius: 100%;
                            background-color:{{$controllerColor}};
                            border:none ;
                            padding:0px 0px'>
            </button>
            @php $ownered = False;@endphp
            @if($stars[$i]['havePlanet'] == 1)
                @php
                    foreach ($planets as $planet) {
                        if ($stars[$i]['id'] == $planet['position'] && $planet['controller'] != '') {
                            $ownered = true;
                            $countryImg = $planet['controller'];
                            $planerName = $planet['name'];
                            break;
                        }
                    }
                @endphp
            @endif
            <button type='button' class='btn btn-default'
                    style='position: absolute;
                    top: {{$y-11}}px; left: {{$x-11}}px; width: 22px;height: 22px;
                    border-radius: 100%;
                    background-color:{{$ownerColor}};
                    border:none ;
                    padding:0px 0px'
                    data-bs-toggle='popover'
                    data-bs-trigger='hover'
                    data-bs-placement='top'
                    data-bs-container ='body'
                    title={{$stars[$i]['name']}}
                              data-bs-html='true'
                    data-bs-content='
                                <p>归属于{{$ownerName}},受控于{{$controllerName}}</p>
                                <p>本星系包含
                                @foreach($stars[$i]['resource'] as $res=>$value)
                                    @if($value == 0)
                                        @php continue;@endphp
                                    @endif
                                    <span class="badge bg-light text-dark"><img src="storage/img/resource/{{$res}}.png"/ width="20px">{{$value}}</span>
                                @endforeach
                                </p>
                                @if ($ownered)
                                    殖民地：<img src="storage/img/countries/{{$countryImg}}.png" width="20px" />{{$planerName}}
                                @endif
                                '
                    onclick="readStar({{$stars[$i]['id']}})">
                @if($type == 'sc_black_hole' || $type == 'sc_pulsar' || $type == 'sc_neutron_star')
                    <img style='position: relative; top: -2.5px; left: -2.5px;' src='{{asset("storage/img/".$type.".png")}}' width='27.5px' />
                @endif
                @if ($ownered)
                    <img style='position: relative; top: -2.5px; left: -2.5px;' src='storage/img/countries/{{$countryImg}}.png' width='27.5px' />
                @endif
            </button>
        @endif
{{--        <button type='button' class='btn btn-default'--}}
{{--                style='position: absolute;--}}
{{--                top: {{$y-25}}px; left: {{$x+10}}px; width: 20px;height: 20px;--}}
{{--                border-radius: 100%;--}}
{{--                background-color:hsla(0,0%,0%,0.00);--}}
{{--                border:none ;--}}
{{--                padding:0px 0px' onclick="setTradeHub({{$stars[$i]['id']}})">--}}
{{--            @if($stars[$i]['isTradeHub'] == 1)--}}
{{--                <img src='storage/img/trade.png' width='17.5px' />--}}
{{--            @endif--}}
{{--        </button>--}}
{{--        <button type='button' class='btn btn-default'--}}
{{--                style='position: absolute;--}}
{{--            top: {{$y}}px; left: {{$x+10}}px; width: 20px;height: 20px;--}}
{{--            border-radius: 100%;--}}
{{--            background-color:hsla(0,0%,0%,0.00);--}}
{{--            border:none ;--}}
{{--            padding:0px 0px'>--}}
{{--            @if($stars[$i]['isCapital'] == 1)--}}
{{--                <img src='storage/img/capital.png' width='17.5px' />--}}
{{--            @endif--}}
{{--        </button>--}}
{{--        @foreach($fleets as $key=>$value)--}}
{{--            @if($value['position'] == $stars[$i]['id'])--}}
{{--                <button class='btn btn-default'--}}
{{--                        style='position: absolute;--}}
{{--                        top: {{$y-25}}px; left: {{$x-30}}px; width: 20px;height: 20px;--}}
{{--                        border-radius: 100%;--}}
{{--                        background-color:hsla(0,0%,0%,0);--}}
{{--                        border:none ;--}}
{{--                        padding:0px 0px'--}}
{{--                        data-bs-toggle='popover'--}}
{{--                        data-bs-trigger='hover'--}}
{{--                        data-bs-placement='top'--}}
{{--                        data-bs-container ='body'--}}
{{--                        data-bs-html='true'--}}
{{--                        title={{$value['name']}}--}}
{{--                    data-bs-content='隶属于{{$value['owner']}}'>--}}
{{--                    @if($value['owner'] == $country)--}}
{{--                        <img src='storage/img/military/fleet_green.png' width='20px' />--}}
{{--                    @else--}}
{{--                        <img src='storage/img/military/fleet_red.png' width='20px' />--}}
{{--                    @endif--}}
{{--                </button>--}}
{{--            @endif--}}
{{--        @endforeach--}}
{{--        @foreach($armys as $key=>$value)--}}
{{--            @if($value['position'] == $stars[$i]['id'])--}}
{{--                <button class='btn btn-default'--}}
{{--                        style='position: absolute;--}}
{{--                    top: {{$y-25}}px; left: {{$x-30}}px; width: 20px;height: 20px;--}}
{{--                    border-radius: 100%;--}}
{{--                    background-color:hsla(0,0%,0%,0.00);--}}
{{--                    border:none ;--}}
{{--                    padding:0px 0px'--}}
{{--                        data-bs-toggle='popover'--}}
{{--                        data-bs-trigger='hover'--}}
{{--                        data-bs-placement='top'--}}
{{--                        data-bs-container ='body'--}}
{{--                        data-bs-html='true'--}}
{{--                        title={{$value['name']}}--}}
{{--                data-bs-content='隶属于{{$value['owner']}}'>--}}
{{--                    @if($value['owner'] == $country)--}}
{{--                        <img src='storage/img/military/army_green.png' width='20px' />--}}
{{--                    @else--}}
{{--                        <img src='storage/img/military/army_red.png' width='20px' />--}}
{{--                    @endif--}}
{{--                </button>--}}
{{--            @endif--}}
{{--        @endforeach--}}
    @endfor
</div>
<canvas id="galaxyMap" >
    <h1>您的浏览器不支持canvas, 请升级后重新访问</h1>
</canvas>
<script type="application/javascript" src="{{asset('js/mapgenerator.js')}}"></script>
<script type="text/javascript">
    var canvas_1 = document.getElementById("galaxyMap");
    var ctx = canvas_1.getContext("2d");
    canvas_1.width = "1920";
    canvas_1.height = "1920";
    ctx.font = "10px sans-serif";
    ctx.strokeStyle = 'black';
    @for ($i=0; $i < count($stars); $i++)
        @php
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
            $stars[$i]['hyperlane'] = json_decode($stars[$i]['hyperlane'],true);
        @endphp
        ctx.lineWidth = 3;
        ctx.strokeStyle = '#66CCFF';
        @foreach ($stars[$i]['hyperlane'] as $key => $value)
        ctx.beginPath();
        ctx.moveTo({{$x}}, {{$y}});
        ctx.lineTo({{$stars[$value["to"]]['x'] * 3 + 960}}, {{$stars[$value["to"]]['y'] * 3 + 960}});
        ctx.closePath();
        ctx.stroke();
        @endforeach
    @endfor
    @for ($i=0; $i < count($stars); $i++)
        @php
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
        @endphp
        @foreach($countrys as $key => $value)
            @if($value['tag'] == $stars[$i]['owner'])
                @php
                    $ownerColor = $value['color'];
                @endphp
            @endif
        @endforeach
        @foreach($countrys as $key => $value)
            @if($value['tag'] == $stars[$i]['controller'])
                @php
                    $controllerColor = $value['color'];
                @endphp
            @endif
        @endforeach
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc({{$x}}, {{$y}}, 13.75, 0, 20 * Math.PI);
        ctx.fillStyle = '{{$controllerColor}}';
        ctx.fill();
        ctx.stroke();
        ctx.lineWidth = 0;
        ctx.beginPath();
        ctx.arc({{$x}}, {{$y}}, 11, 0, 20 * Math.PI);
        ctx.fillStyle = '{{$ownerColor}}';
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = 'white';
        ctx.fillText('{{$stars[$i]['name']}}', {{$x -22.5}}, {{$y +25}});
    @endfor
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
</script>
<div class="modal fade" id="starModal" tabindex="-1" aria-labelledby="starModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div id="starController" style="display: inline"></div>
                <h4 class="modal-title" style="display: inline" id="starName"></h4>
                归属于<div id="starOwner" style="display: inline"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container my-4 py-4 rounded shadow-lg" id="station"></div>
                <div class="container my-4 py-4 rounded shadow-lg" id="planet"></div>
                <div class="container my-4 py-4 rounded shadow-lg">
                    <div class="row">
                        <div class="col-3 container my-4 py-4 rounded shadow-lg">
                            <h6 class="text-center">本星系包含资源</h6>
                            <div class="container my-4">
                                <div id="resource"></div>
                            </div>
                        </div>
                        <div class="col-9 container my-4 py-4 rounded shadow-lg">
                            <h6 class="text-center">本星系包含修正</h6>
                            <div class="container my-4">
                                <div class="row" id="modifier"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                @if($privilege != 3)
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="buildArmy()">招募陆军</button>
                    <button type="button" class="btn btn-primary" data-bs-target="#newDistrictModal" data-bs-toggle="modal" data-bs-dismiss="modal">新建区划</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
