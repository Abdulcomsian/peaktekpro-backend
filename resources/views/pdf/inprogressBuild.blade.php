<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inprogress</title>
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
            <h2 style="text-align: left">Build Details</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="">Build Start Date:</label>
            <input
              style="width: 100%;"
              type="text"
              value="{{ $data['build_start_date'] }}"
            />
          </td>
          <td>
            <label for="">Build End Date:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{ $data['build_end_date'] }}"
            />
          </td>
          <td colspan="2">
            <label for="">Notes:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{ $data['notes'] }}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Status:</label>
            <input style="width: 100%" type="text" value="{{ $data['status'] ? 'Completed' : 'In Progress' }}" />
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>
    <!-- End -->
  </body>
</html>