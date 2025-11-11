<!DOCTYPE html>
<html>

<head>
    <title>CONTRATO DE TRABAJO</title>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: sans-serif;
        }

        body {
            margin: 1cm 2cm 2cm;
            display: block;
            /*font-family: sans-serif;*/
        }

        .first-page-header {
            text-align: center;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3.5cm;
            color: black;
            font-weight: bold;
            text-align: center;
        }


        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            color: black;
            text-align: center;
            line-height: 35px;

        }

        p {
            font-size: 14px;
            text-align: justify;
        }

        image {
            margin-left: 2cm;
        }

        h3 {
            margin-top: 1cm;
            font-size: 15px;
            text-align: center;
        }

        h4 {
            font-size: 14px;
            text-align: center;
        }

        h5 {
            text-align: right;
        }

        h6 {
            margin-bottom: -10px;
        }

        table,
        th,
        td {
            font-size: 10px;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border: none;
        }

        ol {
            list-style-type: lower-latin;
            font-size: 10px;
        }

        ul {
            font-size: 10px;
        }

        li {
            font-size: 14px;
        }

        .signature {
            padding-top: 95px;
        }
    </style>
</head>

<body>
    <div class="first-page-header">
        <img src="{{ public_path('/images/images/logomemo.png') }}" width="800" height="150"/>
    </div>
    <main>
        <h3 style="margin-top: 0.5cm;">CONTRATO DE TRABAJO SUJETO A MODALIDAD POR NECESIDADES DEL MERCADO</h3>
        <div>
            <p>
                Conste por el presente documento el Contrato de Trabajo a plazo fijo bajo la modalidad de "Contrato por
                Necesidad de Mercado", que celebran al amparo del Art. 58 de la Ley de Productividad y Competitividad
                Laboral aprobado por D.S. N°003-97-TR y normas complementarias, de una COMPANY S.A., parte, MOTOR GAS
                N°20472634501, con domicilio fiscal en Jr. San Pedro de Carabayllo N°180, Urbanización Santa Isabel,
                Distrito de Carabayllo, Provincia y Departamento de Lima, debidamente representada por su Gerente
                General, LESLY PAMELA EGOAVIL LOMOTE, identificado con DNI N° 74760461, en su calidad de
                representante legal, a quien en adelante se le denominará EL EMPLEADOR; y, de la otra parte, el Sr.
                <strong>{{ $nombreEmpleado }}</strong>, identificado con DNI N° {{ $dniEmpleado }}, domiciliado en
                {{ $domicilioEmpleado }}, a quien en adelante se le denominará EL
                TRABAJADOR. Ambas partes acuerdan celebrar el presente contrato sujeto a las siguientes cláusulas:
            </p>

            <p><strong><u>PRIMERA: ANTECEDENTES</u></strong></p>
            <p>
                EL EMPLEADOR es una empresa dedicada al servicio de certificación de los vehículos convertidos de
                gasolina a gas en el sistema GLP, GNV y Modificacion que requiere cubrir necesidades de recursos
                humanos debido a un incremento temporal en la demanda del mercado.
            </p>

            <p><strong><u>SEGUNDA: OBJETO DEL CONTRATO</u></strong></p>
            <p>
                El presente contrato tiene como objeto la contratación de EL TRABAJADOR bajo la modalidad de necesidades
                del mercado en virtud de la necesidad de cubrir un aumento temporal de demanda en los servicios de
                certificación de los vehículos convertidos de gasolina a gas en el sistema GLP, conforme al Artículo 58
                del Texto Único Ordenado del Decreto Legislativo N°728, Ley de Productividad y Competitividad Laboral,
                aprobado por D.S. N°003-97-TR.
            </p>
            <p>
                EL TRABAJADOR desempeñará el cargo de {{ $cargo }}, en el local de trabajo ubicado en Jr. San
                Pedro
                de Carabayllo N° 180, Urbanización Santa Isabel, Distrito de Carabayllo, Provincia y Departamento de
                Lima.
            </p>

            <p><strong><u>TERCERA: DURACIÓN Y RENOVACIÓN</u></strong></p>
            <p>
                El contrato tendrá una duración de un {{ $duracion }}, iniciando el {{ $fechaInicio }} y culminando el {{ $fechaExpiracion }}, extinguiéndose automáticamente en dicha fecha, salvo que las partes acuerden por
                escrito su renovación.
            </p>

            <p><strong><u>CUARTA: HORARIO DE TRABAJO</u></strong></p>
            <p>
                EL TRABAJADOR deberá cumplir una jornada diaria de ocho (08) horas hasta completar las cuarenta y cinco
                (45) horas semanales, teniendo un descanso para refrigerio de una (1) horas, sujetándose
                a los turnos de trabajo que establezca EL EMPLEADOR.
            </p>
            <p>
                EL EMPLEADOR podrá modificar los horarios de trabajo de acuerdo con las necesidades operativas de la
                empresa, respetando los límites legales y comunicando cualquier cambio con antelación razonable.
            </p>

            <p><strong><u>QUINTA: REMUNERACIÓN</u></strong></p>
            <p>
                EL EMPLEADOR se obliga a pagar a EL TRABAJADOR la suma de S/. {{ $pago }}
                como remuneración mensual, de la cual se deducirán las aportaciones y descuentos legales aplicables.
            </p>

            <p><strong><u>SEXTA: OBLIGACIONES DE LAS PARTES</u></strong></p>
            <p>Obligaciones del EMPLEADOR:</p>
            <ul>
                <li>Inscribir a EL TRABAJADOR en la Planilla Electrónica (PDT 601) y comunicar el presente contrato a la
                    Autoridad Administrativa de Trabajo.</li>
                <li>Proveer a EL TRABAJADOR los materiales, equipos y herramientas necesarios para el desempeño de sus
                    funciones.</li>
                <li>Respetar los derechos laborales de EL TRABAJADOR conforme a la ley vigente.</li>
            </ul>

            <p>Obligaciones del TRABAJADOR:</p>
            <ul>
                <li>Desempeñar sus funciones como {{ $cargo }} y cumplir con las normas del Centro de Trabajo,
                    Reglamento Interno y disposiciones legales aplicables.</li>
                <li>Responder por los daños y perjuicios ocasionados a EL EMPLEADOR por negligencia grave o
                    incumplimiento de sus deberes laborales.</li>
                <li>Mantener la confidencialidad sobre la información a la que tenga acceso durante la relación laboral.
                </li>
                <li>Disponer su traslado temporal a otras ciudades del país si lo requiere EL EMPLEADOR.</li>
            </ul>

            <p><strong><u>SÉPTIMA: RESOLUCIÓN DEL CONTRATO</u></strong></p>
            <p><strong>1. Resolución por conclusión de la necesidad del mercado:</strong></p>
            <p>Este contrato podrá ser terminado de forma anticipada por EL EMPLEADOR cuando se haya cumplido la
                necesidad del mercado que justificó la contratación. En este caso, EL EMPLEADOR notificará por escrito a
                EL TRABAJADOR con una antelación mínima de 1 mes.</p>

            <p><strong>2. Incumplimiento contractual:</strong></p>
            <p>EL EMPLEADOR podrá resolver el contrato de pleno derecho en caso de incumplimiento de las obligaciones
                pactadas por parte de EL TRABAJADOR y/o cualquier incumplimiento de las cláusulas de este contrato.</p>

            <p><strong>3. Resolución por causas legales:</strong></p>
            <p>EL EMPLEADOR podrá resolver el contrato sin derecho a indemnización por causas justas contempladas en los
                artículos 22, 23, 24 y 25 del Texto Único Ordenado del Decreto Legislativo N°728:</p>
            <ul>
                <p>a) Causas relacionadas con la capacidad del trabajador (Artículo 23 del D. L. N°728).</p>
                <p>b) Causas justas de despido relacionadas con la conducta del trabajador (Artículo 24 Y 25 del D. L.
                    N°728).</p>
            </ul>

            <p><strong>4. Extinción automática: </strong>El contrato finalizará automáticamente en la fecha de
                vencimiento sin necesidad de aviso previo, conforme a la cláusula tercera.</p>
            <p>En todos los casos, EL EMPLEADOR abonará a EL TRABAJADOR los beneficios sociales correspondientes
                conforme a la legislación laboral vigente.</p>

            <p><strong><u>OCTAVO: CONFIDENCIALIDAD</u></strong></p>
            <p>EL TRABAJADOR se compromete a mantener estricta confidencialidad sobre toda información relacionada con
                los clientes, procesos, metodologías y cualquier otra información a la que tenga acceso durante la
                relación laboral como los clientes. Esta obligación se extenderá por un periodo de un (01) año después
                de la terminación del contrato.</p>
            <p>En caso de terminación anticipada o resolución contractual EL TRABAJADOR deberá continuar sujeto a las
                disposiciones de no competencia establecidas en esta cláusula. El incumplimiento de esta disposición
                también estará sujeto a las sanciones señaladas en la misma.</p>

            {{--
            <p><strong><u>NOVENO: NO COMPETENCIA</u></strong></p>
            <p>EL TRABAJADOR se obliga, por un periodo de un (01) año posterior a la terminación del contrato, a no
                prestar servicios a empresas competidoras de EL EMPLEADOR que operen en el mismo sector y ámbito
                geográfico.</p>
            <p>EMPLEADOR que operen en el mismo sector y ámbito geográfico.
                En caso de terminación anticipada o resolución contractual EL TRABAJADOR deberá continuar sujeto a las
                disposiciones de no competencia establecidas en esta cláusula. El incumplimiento de esta disposición
                también estará sujeto a las sanciones señaladas en la misma.</p>
            --}}

            <p><strong><u>NOVENO: SOLUCIÓN DE CONFLICTOS</u></strong></p>
            <p>Cualquier controversia derivada del presente contrato será sometida, en primer lugar, a un proceso de
                conciliación. De no llegarse a un acuerdo, las partes se someten a la jurisdicción de los jueces y
                tribunales de Lima.</p>

            <p><strong><u>DÉCIMO: DISPOSICIONES FINALES</u></strong></p>
            <p>En todo lo no previsto en este contrato, se estará a las disposiciones contenidas en el Texto Único
                Ordenado del Decreto Legislativo N°728 y demás normas complementarias aplicables.</p>
            <p>Conformes, con todas las cláusulas del presente contrato, firman las partes por duplicado, el dia
                {{ $fechaInicio }}</p>

            </div>

        <table style="width: 100%;">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ public_path('/images/firmaIng.png') }}" width="200" height="95" />
                    <h4>_________________________</h4>
                    <h4><strong>Lopez Henriquez Spasoje Bratzo</strong> <br>El Empleador </h4>
                </td>
                <td style="text-align: center;" class="signature">
                    <h4>_________________________</h4>
                    <h4><strong>{{ $nombreEmpleado }}</strong> <br>{{ $dniEmpleado }} </h4>
                </td>
            </tr>
        </table>
    </main>
    <footer>
    </footer>
</body>

</html>
