
          @if (@isset($data) && !@empty($data))

          <table id="example2" class="table table-bordered table-hover">
            <thead class="custom_thead">

                <th>name </th>
                <th> email </th>
                <th>  phone </th>

                <th>active</th>
          <th></th>

            </thead>
            <tbody>
                @foreach ($data as $info )
                   <tr>

                    <td>{{ $info->name }}</td>
                    <td>{{ $info->email }}</td>


                    <td>{{ $info->phone }}</td>


                    <td>@if($info->is_active==1) مفعل @else معطل @endif</td>

                <td>

               <a href="{{ route('admin.customer.edit',$info->id) }}" class="btn btn-sm  btn-primary">edit</a>
               <a href="{{ route('admin.customer.delete',$info->id) }}" class="btn btn-sm are_you_shue  btn-danger">delete</a>

                </td>


                  </tr>

                @endforeach



                   </tbody>
             </table>
      <br>
      <div class="col-md-12" id="ajax_pagination_in_search">
         {{ $data->links() }}
      </div>

           @else
           <div class="alert alert-danger">
there is no customer found
           </div>
                 @endif
