<script type="application/javascript" src="{{asset('js/planet.js')}}"></script>
<x-app-layout>
    @include('components.header')
    <div class="container my-4">
        <h1 class="text-center">星球</h1>
    </div>
    <div class="container mt-4 py-4 rounded shadow-lg mb-5" style="background: #FFFFFF">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <div class="row">
                    <h5 class="col text-center">星球</h5>
                    <h5 class="col text-center">位置</h5>
                    <h5 class="col text-center">类型</h5>
                    <h5 class="col text-center">资源</h5>
                    <h5 class="col text-center">详情</h5>
                </div>
            </li>
            @foreach($planets as $planet)
                <li class="list-group-item">
                    <div class="row">
                        @if($privilege==3)
                            <p class="col text-center">{{$planet['name']}}</p>
                        @elseif($privilege<=2)
                            <div class="col">
                                <input type="text" class="form-control" id="planetName-{{$planet['id']}}" value="{{$planet['name']}}" onchange="changePlanetName({{$planet['id']}})"/>
                            </div>
                        @endif
                        <p class="col text-center">{{$planet['position']}}</p>
                        <p class="col text-center">{{$planet['type']}}</p>
                        <p class="col text-center">
                            @foreach($planet['product']['produce'] as $key=>$value)
                                @if($planet['product']['produce'][$key]-$planet['product']['upkeep'][$key]>0)
                                    <span class="badge bg-success text-dark" style="width: 15%"><img src="storage/img/resource/{{$key}}.png" width="20px">
                                    {{$planet['product']['produce'][$key]-$planet['product']['upkeep'][$key]}}
                                    </span>
                                @elseif($planet['product']['produce'][$key]-$planet['product']['upkeep'][$key]<0)
                                    <span class="badge bg-danger text-dark" style="width: 15%"><img src="storage/img/resource/{{$key}}.png" width="20px">
                                    {{$planet['product']['produce'][$key]-$planet['product']['upkeep'][$key]}}
                                    </span>
                                @endif
                            @endforeach
                        </p>
                        <p class="col text-center">
                            <button class="btn btn-primary" type="button" onclick="readPlanet({{$planet['id']}},{{$privilege}})">详情</button>
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
<div class="modal fade" id="planetModal" tabindex="-1" aria-labelledby="planetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planetName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container my-4 py-4 rounded shadow-lg">
                    <div class="row">
                        <div class="col my-4 py-4">
                            <div class="container my-4">
                                <h5 class="text-center">星球生成</h5>
                            </div>
                            <div class="container my-4" id="marketProduct"></div>
                        </div>
                        <div class="col my-4 py-4">
                            <div class="container my-4">
                                <h5 class="text-center">星球消耗</h5>
                            </div>
                            <div class="container my-4" id="countryProduct"></div>
                        </div>
                    </div>
                </div>
                <div class="container my-4 py-4 rounded shadow-lg">
                    <div class="row">
                        <div class="col my-4 py-4">
                            <h6 class="text-center">修正</h6>
                            <div class="container my-4">
                                <div class="row" id="pops"></div>
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

