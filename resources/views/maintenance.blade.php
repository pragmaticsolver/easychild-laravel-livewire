<!doctype html>
<html>
<head>
    <title>easychild - Kita digital</title>
    <meta name="description" content="easychild - Kita digital - Wartungsarbeiten">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>

        @font-face {
            font-family: "Quicksand";
            font-style: normal;
            font-weight: 400;
            src: url("/fonts/quicksand-v21-latin-regular.eot");
            src: local(""), url("/fonts/quicksand-v21-latin-regular.eot?#iefix") format("embedded-opentype"), url("/fonts/quicksand-v21-latin-regular.woff2") format("woff2"), url("/fonts/quicksand-v21-latin-regular.woff") format("woff"), url("/fonts/quicksand-v21-latin-regular.ttf") format("truetype"), url("/fonts/quicksand-v21-latin-regular.svg#Quicksand") format("svg");
        }

        body {
            margin: 0; 
            padding: 0;
            font-family: Quicksand, Helvetica, sans-serif;
            height: 100%;
            color: white;
        }
        .bg {
            z-index: -1;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:#FB758E url('/img/update-screen-fhd.jpg') no-repeat fixed 0% 100%; 
            background-size:cover; 
        }
        .wrapper {
            width: 100%; 
            padding: 3em;
            box-sizing: border-box;
            background:rgba(0,0,0,0.5)
        }
        h1 {
            font-size: 2.5em;
        }
        p {
            font-size: 1em;
        }
        a.btn {
            display: block;
            text-align: center;
            padding: 0.5em;
            text-decoration: none;
            color: white;
            background-color: rgba(255,255,255,0.1);
        }

        @media screen and (min-width: 993px) {
            body {
                display: block;
            }
            .bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background:#FB758E url('/img/update-screen-fhd.jpg') no-repeat fixed 0% 100%; 
                background-size:cover; 
            }
            .wrapper {
                position:absolute; 
                top:0; 
                left:50%; 
                height:100%; 
                width: 50%; 
                padding: 3em;
                box-sizing: border-box;
                background:rgba(0,0,0,0.5)
            }
            h1, {
                font-size: 5vw;
            }
            p {
                font-size: 2vw;
            }
            a {
                font-size: 2vw;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Niemand da...</h1>
        <p>Aber keine Sorge, wir sind in wenigen Augenblicken besser denn je und vielleicht auch mit ein paar neuen Funktionen zurück.</p>
        <p>Dein easychild-Team!</p>
        <a class="btn" href="#" onclick="location.reload();">❯ Nochmal probieren</a>
    </div>
    <div class="bg">&nbsp;</div>
</body>
</html>