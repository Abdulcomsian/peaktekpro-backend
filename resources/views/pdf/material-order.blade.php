<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <style>
      * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }
      body {
        font-family: sans-serif;
        color: black;
      }
      h2 {
        font-size: 20px;
        text-transform: uppercase;
        margin-bottom: 15px;
      }
      p {
        color: #333;
      }
      input {
        padding-inline: 10px;
        padding-block: 13px;
        color: #666;
        border: 1px solid lightgray;
        border-radius: 5px;
        background-color: #66666614;
      }
      label {
        font-size: 1rem;
        color: #666;
        margin-bottom: 5px;
        display: inline-block;
      }
      table:not(.header-image-table) {
        border-spacing: 20px;
      }
      table tr td {
        padding-block: 0.2rem;
        /* border: 5px solid #000; */
        width: 25%;
      }
      .page-break {
        page-break-before: always;
      }

      .list {
        padding-left: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
      }
      .list li {
        line-height: 1.5;
      }
    </style>
  </head>
  <body>
    <table class="header-image-table" style="margin-bottom: 60px">
      <tbody>
        <tr>
          <td>
            <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/pdf_header.png')))}}" width="1500"/>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th colspan="6">
            <h2 style="text-align: left">Material Order</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="">name:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->job->name}}"
            />
          </td>
          <td>
            <label for="">email:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->job->email}}"
            />
          </td>
          <td colspan="2">
            <label for="">phone:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->job->phone}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Street:</label>
            <input style="width: 100%" type="text" value="{{$data->street}}" />
          </td>
          <td>
            <label for="">City:</label>
            <input style="width: 100%" type="text" value="{{$data->city}}" />
          </td>
          <td>
            <label for="">State / province:</label>
            <input style="width: 100%" type="text" value="{{$data->state}}" />
          </td>
          <td>
            <label for="">Zip Code / Postal Code:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->zip_code}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Insurance:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->insurance}}"
            />
          </td>
          <td>
            <label for="">Claim Number:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->claim_number}}"
            />
          </td>
          <td colspan="2">
            <label for="">Policy Number:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->policy_number}}"
            />
          </td>
        </tr>
      </tbody>
    </table>
    <!-- section4 -->

    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th colspan="6">
            <h2 style="text-align: left">Delivry Information</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="">Date</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->date_needed}}"
            />
          </td>
          <td>
            <label for="">Square Count</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->square_count}}"
            />
          </td>
          <td>
            <label for="">Total perimeter LF</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->total_perimeter}}"
            />
          </td>
          <td>
            <label for="">Ridge LF</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->ridge_lf}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Build Date</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->build_date}}"
            />
          </td>
          <td>
            <label for="">Valley SF</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->valley_sf}}"
            />
          </td>
          <td>
            <label for="">Hip and ridge LF</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->hip_and_ridge_lf}}"
            />
          </td>
          <td>
            <label for="">Drip Edge LF </label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->drip_edge_lf}}"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <table style="width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <label for="">Material</label>
          </td>
          <td>
            <label for="">Quantity</label>
          </td>
          <td>
            <label for="">Color</label>
          </td>
          <td>
            <label for="">Order key</label>
          </td>
        </tr>
        @foreach($data->materials as $material)
        <tr>
          <td>
            <input
              style="width: 100%"
              type="text"
              value="{{$material->material}}"
            />
          </td>
          <td>
            <input
              style="width: 100%"
              type="text"
              value="{{$material->quantity}}"
            />
          </td>
          <td>
            <input
              style="width: 100%"
              type="text"
              value="{{$material->color}}"
            />
          </td>
          <td>
            <input
              style="width: 100%"
              type="text"
              value="{{$material->order_key}}"
            />
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>
    <!-- End -->
  </body>
</html>
