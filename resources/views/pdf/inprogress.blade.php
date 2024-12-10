<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inprogress PDF</title>
  <style>
    * {
      padding: 0;
      margin: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
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
    table {
      width: 100%;
      border-spacing: 15px;
      margin-bottom: 20px;
    }
    table th, table td {
      text-align: left;
      padding: 5px;
      vertical-align: top;
    }
    .section-title {
      margin-top: 20px;
      font-size: 18px;
      text-transform: uppercase;
      font-weight: bold;
      border-bottom: 1px solid #000;
      padding-bottom: 5px;
    }
    .photo {
      margin: 10px 0;
      text-align: center;
    }
    .photo img {
      max-width: 100%;
      height: auto;
    }
    .signature {
      text-align: center;
    }
    .signature img {
      max-width: 200px;
      height: auto;
      margin-top: 10px;
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

  <h2>Inprogress Build Report</h2>

  <!-- Job Information -->
  <div class="section">
    <h3 class="section-title">Build Details</h3>
    <table>
      <tr>
        <th>Build Start Date:</th>
        <td>{{ $data['build_start_date'] }}</td>
      </tr>
      <tr>
        <th>Build End Date:</th>
        <td>{{ $data['build_end_date'] }}</td>
      </tr>
      <tr>
        <th>Notes:</th>
        <td>{{ $data['notes'] }}</td>
      </tr>
      <tr>
        <th>Status:</th>
        <td>{{ $data['status'] ? 'Completed' : 'In Progress' }}</td>
      </tr>
    </table>
  </div>

  <!-- Saved Photos -->
  <div class="section">
    <h3 class="section-title">Saved Photos</h3>
    <table>
      @foreach ($saved_photos as $photo)
        <tr>
          <th>Label:</th>
          <td>{{ $photo['labels'] }}</td>
        </tr>
        <tr>
          <th>Image:</th>
          <td class="photo">
            <img src="{{ public_path($photo['image_paths']) }}" alt="Photo">
          </td>
        </tr>
      @endforeach
    </table>
  </div>

  <!-- Signatures -->
  <div class="section">
    <h3 class="section-title">Signatures</h3>
    <table>
      <tr>
        <th>Production Sign URL:</th>
        <td class="signature">
          <img src="{{ public_path($data['production_sign_url']) }}" alt="Production Signature">
        </td>
      </tr>
      <tr>
        <th>Homeowner Signature:</th>
        <td class="signature">
          <img src="{{ public_path($data['homeowner_signature']) }}" alt="Homeowner Signature">
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
