<!DOCTYPE html>
<html>

<head>
    <title>Medication List</title>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.6/cerulean/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="content">

        <div class="row">
            <div class="col-md-5 pull-left">
                <div><b style="color:#555555;margin:0;font-size:16px;">{% $client->LastName %},
                        {% $client->FirstName %}</b></div>
                <div><b>DOB:</b> {% date('M d, Y', strtotime($client->DOB)) %}</div>
            </div>

            @if ($provider)
            <div class="col-md-5 text-right pull-right">
                <div><b>{% $provider->Name %}</b></div>
                <div>{% $provider->AddressLine1 %}</div>
                @if ($provider->showCountyDistrict)
                <div>{% $provider->CountyDistrict %}, {% $provider->AddressCity %}, {% $provider->AddressZipCode %}
                </div>
                @else
                <div>{% $provider->AddressCity %}, {% $provider->AddressStateCode %} {% $provider->AddressZipCode %}
                </div>
                @endif
                <div>Phone: {% formatPhone($provider->PhoneWork) %}</div>
                <div>Fax: {% formatPhone($provider->FaxNumber) %}</div>
            </div>
            @endif

        </div>

        <div class="card medication-card">
            @if($options['active'])
            <div class="header">
                <h3 class="title">Active Medications</h3>
            </div>

            <table class="table m-b-0">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Reason</th>
                        <th>Instructions</th>
                        <th>Modified</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medications as $medication)
                    @if($medication->Active)
                    <tr>
                        <td>{% $medication->MedicationDosage %}</td>
                        <td>{% $medication->Classification %}</td>
                        <td>{% $medication->Frequency %} - {% $medication->Route %}</td>
                        <td>{% $medication->LastChangedDate %}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($options['inactive'])
            <div class="header">
                <h3 class="title">Discontinued Medications</h3>
            </div>

            <table class="table m-b-0">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Reason</th>
                        <th>Instructions</th>
                        <th>Modified</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medications as $medication)
                    @if(!$medication->Active)
                    <tr>
                        <td>{% $medication->MedicationDosage %}</td>
                        <td>{% $medication->Classification %}</td>
                        <td>{% $medication->Frequency %} - {% $medication->Route %}</td>
                        <td>{% $medication->LastChangedDate %}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @endif

        </div>
    </div>



</body>

</html>
