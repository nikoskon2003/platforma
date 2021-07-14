<?php
http_response_code(404);
include __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<meta charset="utf-8" />
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $siteName; ?> | 404</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Noto+Sans&display=swap');
        @import url('https://fonts.googleapis.com/css?family=Roboto&display=swap');
        body {
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        #main-div {
            position: absolute;
            z-index: 100;
            width: 100%;
            top: 10%;
            text-align: center;
        }
        #main-div p {
            color: black;
            width: 100%;
            margin: 0 auto;
            text-align: center;
            font-family: 'Roboto';
            font-size: calc(3vw + 1vh);
        }
        #main-div div {
            width: 60%;
            margin: 0 auto;
            margin-top: 20px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            color: black;
            border-radius: 10px;
            font-family: 'Noto Sans';
            font-size: calc(1.5vw + 0.5vh);
        }
    </style>
</head>

<body>
    <!--
    Fun fact: Αν τρέξεις "stars = makeStars(1000);" στην κονσόλα, τα αστέρια θα αυξηθούν!

    Α.. ξέχασα.. γιατί είσαι στον Inspector είπαμε; Τέλος πάντων... πάρε ένα μπισκότο για τον κόπο σου: 🍪 -->
    <div id="main-div">
        <p id="title">404<br>Δεν Βρέθηκε</p>
        <div>Χαθήκατε;<br>Επιστροφή στην <a href="<?= strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http'; ?>://<?= $_SERVER['SERVER_NAME']; ?>">Αρχική Σελίδα</a></div>
    </div>
    <canvas id="canvas" style="width: 100%; height: 100%; padding: 0;margin: 0;"></canvas>
  
    <script>
        const canvas = document.getElementById("canvas");
        const c = canvas.getContext("2d");

        document.getElementById('title').style.color = "#fff";

        let w;
        let h;

        const setCanvasExtents = () => {
            w = document.body.clientWidth;
            h = document.body.clientHeight;
            canvas.width = w;
            canvas.height = h;
        };

        setCanvasExtents();

        window.onresize = () => {
            setCanvasExtents();
        };

        const makeStars = count => {
            const out = [];
            for (let i = 0; i < count; i++) {
                const s = {
                    x: Math.random() * 1600 - 800,
                    y: Math.random() * 900 - 450,
                    z: Math.random() * 1000
                };
                out.push(s);
            }
            return out;
        };

        let stars = makeStars(200);

        const clear = () => {
            c.fillStyle = "black";
            c.fillRect(0, 0, canvas.width, canvas.height);
        };

        const putPixel = (x, y, brightness) => {
            const intensity = brightness * 255;
            const rgb = "rgb(" + intensity + "," + intensity + "," + intensity + ")";
            c.beginPath();
            c.arc(x, y, Math.max(1.5*brightness, 0), 0, Math.PI * 2, false);
            c.fillStyle = rgb;
            c.fill();
            c.strokeStyle = rgb;
            c.stroke();
        };

        const moveStars = distance => {
            const count = stars.length;
            for (var i = 0; i < count; i++) {
                const s = stars[i];
                s.z -= distance;
                while (s.z <= 1) {
                    s.z += 1000;
                }
            }
        };

        let prevTime;
        const init = time => {
            prevTime = time;
            requestAnimationFrame(tick);
        };

        const tick = time => {
            let elapsed = time - prevTime;
            prevTime = time;

            moveStars(elapsed * 0.1);

            clear();

            const cx = w / 2;
            const cy = h / 2;

            const count = stars.length;
            for (var i = 0; i < count; i++) {
                const star = stars[i];

                const x = cx + star.x / (star.z * 0.002);
                const y = cy + star.y / (star.z * 0.002);

                if (x < 0 || x >= w || y < 0 || y >= h) {
                    continue;
                }

                const d = star.z / 1000.0;
                const b = 1 - d * d;

                putPixel(x, y, b);
            }

            requestAnimationFrame(tick);
        };

        requestAnimationFrame(init);
    </script>
</body>
</html>