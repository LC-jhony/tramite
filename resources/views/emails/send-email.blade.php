<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Registro de Trámite</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1e40af;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #374151;
        }
        .success-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .success-icon span {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            line-height: 60px;
            font-size: 30px;
        }
        .document-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .document-info h3 {
            margin: 0 0 15px;
            color: #1e40af;
            font-size: 16px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .document-info td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .document-info tr:last-child td {
            border-bottom: none;
        }
        .document-info td:first-child {
            font-weight: 500;
            color: #64748b;
            width: 45%;
        }
        .document-info td:last-child {
            color: #1e293b;
            font-weight: 600;
        }
        .subject-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .subject-box .label {
            font-size: 12px;
            font-weight: 600;
            color: #92400e;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .subject-box .content {
            color: #78350f;
            margin: 0;
            padding: 0;
        }
        .alert {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .alert strong {
            display: block;
            margin-bottom: 5px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 25px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer .org-name {
            font-weight: 600;
            color: #1e40af;
            font-size: 14px;
        }
        .footer .divider {
            margin: 15px 0;
            border-top: 1px dashed #cbd5e1;
        }
        .status-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .priority-high {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .priority-medium {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✓ Trámite Registrado Exitosamente</h1>
            <p>Mesa de Partes - Sistema de Trámite Documentario</p>
        </div>
        
        <div class="content">
            <div class="success-icon">
                <span>✓</span>
            </div>
            
            <p class="greeting">
                Estimado/a <strong>{{ $customer->full_name }}</strong>:
            </p>
            
            <p>
                Su trámite ha sido registrado exitosamente en nuestra Mesa de Partes. 
                Le proporcionamos los detalles de su solicitud a continuación:
            </p>
            
            <div class="document-info">
                <h3>📋 Detalles del Trámite</h3>
                <table>
                    <tr>
                        <td>Número de Caso:</td>
                        <td>{{ $document->case_number }}</td>
                    </tr>
                    <tr>
                        <td>Número de Documento:</td>
                        <td>{{ $document->document_number }}</td>
                    </tr>
                    <tr>
                        <td>Tipo de Documento:</td>
                        <td>{{ $document->type->name ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td>Fecha de Recepción:</td>
                        <td>{{ \Carbon\Carbon::parse($document->reception_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Fecha Límite de Respuesta:</td>
                        <td>
                            @if($document->response_deadline)
                                {{ \Carbon\Carbon::parse($document->response_deadline)->translatedFormat('d F Y') }}
                            @else
                                No especificada
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Oficina de Destino:</td>
                        <td>{{ $document->currentOffice->name ?? 'Mesa de Partes' }}</td>
                    </tr>
                    <tr>
                        <td>Origen:</td>
                        <td>{{ $document->origen }}</td>
                    </tr>
                    <tr>
                        <td>Estado:</td>
                        <td><span class="status-badge">Registrado</span></td>
                    </tr>
                </table>
            </div>
            
            <div class="subject-box">
                <div class="label">📄 Asunto del Trámite:</div>
                <div class="content">
                    {!! $document->subject !!}
                </div>
            </div>
            
            <div class="alert">
                <strong>⚠️ Información Importante:</strong>
                <ul style="margin: 10px 0 0; padding-left: 20px;">
                    <li>Recuerde el número de caso <strong>{{ $document->case_number }}</strong> para hacer seguimiento a su trámite.</li>
                    <li>La fecha límite de respuesta es informativa y puede variar según el tipo de procedimiento.</li>
                    <li>Se le notificará al correo electrónico registrado cuando haya alguna actualización.</li>
                </ul>
            </div>
            
            @if($customer->phone || $customer->email)
            <div class="document-info">
                <h3>📞 Datos de Contacto</h3>
                <table>
                    @if($customer->phone)
                    <tr>
                        <td>Teléfono:</td>
                        <td>{{ $customer->phone }}</td>
                    </tr>
                    @endif
                    @if($customer->email)
                    <tr>
                        <td>Correo:</td>
                        <td>{{ $customer->email }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p class="org-name">Municipalidad - Mesa de Partes</p>
            <div class="divider"></div>
            <p>Este es un correo electrónico automático, por favor no responda directamente a este mensaje.</p>
            <p>Si tiene alguna consulta, comuníquese con nuestra área de atención al ciudadano.</p>
        </div>
    </div>
</body>
</html>
