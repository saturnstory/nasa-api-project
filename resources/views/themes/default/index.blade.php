@extends("themes.default.layout.master")
@section("css")

@stop

@section("content")
    <div class="container">

        <form>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="start_date">From</label>
                    <input type="text" value="{{$request->input("start_date")}}" class="form-control" name="start_date" id="start_date" placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group col-md-4">
                    <label for="end_date">To</label>
                    <input type="text" value="{{$request->input("end_date")}}" class="form-control" name="end_date" id="end_date" placeholder="YYYY-MM-DD">
                </div>
            </div>
            <input type="hidden" name="page" value="{{$request->input("page")}}">

            <button type="button" id="flush-btn" class="btn btn-warning">FLUSH</button>
            <button type="button" id="import-btn" class="btn btn-secondary">IMPORT</button>

            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        @if(!count($photos->photos))
        <div class="alert alert-danger" role="alert">
            No records found to be listed
        </div>
        @endif

        <table class="table">
            <thead class="thead-dark">
            <tr>
                <th scope="col">id</th>
                <th scope="col">Earth Date</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($photos->photos as $photo)
            <tr>
                <th scope="row">{{$photo->id}}</th>
                <td>{{$photo->earth_date}}</td>
                <td>@if(in_array($photo->id, $inIds))<span class="badge badge-success">imported</span> @else Waiting @endif </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop

@section("js")
    <script type="text/javascript">
        $(function(){
            $("#import-btn").click(function () {
                var startDate = $('input[name=start_date]').val();
                var endDate = $('input[name=end_date]').val();
                var page = $('input[name=page]').val();
                var uri = "import?start_date="+startDate+"&end_date="+endDate+"&page="+page;

                $.getJSON(uri, function (jdata) {
                    alert(jdata.mess);
                    location.reload();
                });

                return false;
            });

            $("#flush-btn").click(function () {
                var startDate = $('input[name=start_date]').val();
                var endDate = $('input[name=end_date]').val();

                var uri = "destroy?start_date="+startDate+"&end_date="+endDate;

                $.getJSON(uri, function (jdata) {
                    alert(jdata.mess);
                    location.reload();
                });

                return false;
            });
        });
    </script>
@stop
