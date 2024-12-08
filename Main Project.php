<!DOCTYPE html>
<html lang="en">
    <!--256,128-->

<!--setting cookie to see if they have visited within 5 mins-->
<?php
    if(isset($_COOKIE["username"])){
        $userName = $_COOKIE["username"];
    } else{
        setcookie("menuCookie", "", time()+10);
    }

    if(isset($_COOKIE["resetCookie"]) && !isset($_COOKIE["menuCookie"])){
        $cookieExists = true;
    } else{
        setcookie("menuCookie", "", time()-1);
        $cookieExists = false;
        setcookie("resetCookie", "true",time()+60*5);
    }

    if(isset($_COOKIE["username"])){
        $userName = $_COOKIE["username"];
    } else{
        $userName = "Cookie reset";
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main canvas project</title>

    <link rel="stylesheet" href="Main project.css">

    <!--google fonts used in project-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">

</head>

<body>
    <canvas id="mainCanvas" style="border: 1px solid black;"></canvas>

    <div id="usernameContainer">
        <h3>Username:</h3>
        <input type="text" id="nameInput" style="height: 20px; margin-left: 10px; margin-right: 10px;">
        <button id="submitName">Play</button>
    </div>
    <div id="errorText"></div>
    <div id="resetContainer">
        <button id='resetButton' style='font-size: 20px;'>Play again</button>
        <button id='menuButton' style='font-size: 20px;'>Main menu</button>
    </div>

    <!--outputting top 10 leaderboard from postgresql-->
    <?php
        include("get_db_connection.php");
        $query = "SELECT * FROM leaderboard ORDER BY score DESC LIMIT 10";
        $conn = get_db_connection();
        $stmt =$conn->prepare($query);
        $stmt->execute();

        echo "<table id='leaderboard'><tr><th>Username</th><th>Score</th></tr>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['score']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        $conn = null;
    ?>

    <script>
        const canvas = document.getElementById("mainCanvas");
        const gameName = "PIGEON PETE";
        let ctx = canvas.getContext("2d");

        //background images
        let scrollBG1 = new Image();
        scrollBG1.src = "free-city-backgrounds-pixel-art/city 5/resized/1.png"
        let scrollBG2 = new Image();
        scrollBG2.src = "free-city-backgrounds-pixel-art/city 5/resized/2.png"
        let scrollBG3 = new Image();
        scrollBG3.src = "free-city-backgrounds-pixel-art/city 5/resized/3.png"
        let scrollBG4 = new Image();
        scrollBG4.src = "free-city-backgrounds-pixel-art/city 5/resized/4.png"
        let scrollBG5 = new Image();
        scrollBG5.src = "free-city-backgrounds-pixel-art/city 5/resized/5.png"

        //pigeon animation images
        let pigeonImage = new Image();
        pigeonImage.src = "My pigeons.png";

        //item images
        let beanCanImage = new Image();
        beanCanImage.src = "thrownItems/beanCan.png";
        let beerBottleImage = new Image();
        beerBottleImage.src = "thrownItems/beerBottle.png";
        let brokenMugImage = new Image();
        brokenMugImage.src = "thrownItems/brokenMug.png";
        let cardboardBoxImage = new Image();
        cardboardBoxImage.src = "thrownItems/cardboardBox.png";
        let drinkCanImage = new Image();
        drinkCanImage.src = "thrownItems/drinkCan.png";
        let drinkCupImage = new Image();
        drinkCupImage.src = "thrownItems/drinkCup.png";
        let fishCanImage = new Image();
        fishCanImage.src = "thrownItems/fishCan.png";
        let glassBottleImage = new Image();
        glassBottleImage.src = "thrownItems/glassBottle.png";
        let glassJarImage = new Image();
        glassJarImage.src = "thrownItems/glassJar.png";
        let milkCartonImage = new Image();
        milkCartonImage.src = "thrownItems/milkCarton.png";
        let newsPapersImage = new Image();
        newsPapersImage.src = "thrownItems/newsPapers.png";
        let paperBagImage = new Image();
        paperBagImage.src = "thrownItems/paperBag.png";
        let pizzaBoxImage = new Image();
        pizzaBoxImage.src = "thrownItems/pizzaBox.png";
        let plasticBottleImage = new Image();
        plasticBottleImage.src = "thrownItems/plasticBottle.png";
        let sprayCanImage = new Image();
        sprayCanImage.src = "thrownItems/sprayCan.png";
        let washingStuffImage = new Image();
        washingStuffImage.src = "thrownItems/washingStuff.png";

        //username
        let username;

        //background scroll variables
        let bgWidth1 = 0;
        let bgWidth2 = 0;
        let bgWidth3 = 0;
        let bgWidth4 = 0;
        let bgWidth5 = 0;
        let scrollSpeed = 1;

        //pigeon sheet coordinates
        let sheetX = 64*1;
        let sheetY = 64*0;
        let sheetH = 64;
        let sheetW = 64;
        let birdScale = sheetH*1.5;

        //pigeon animation change variables
        let lastFrame = 64*0;
        let loopCount = 0;

        //game starting variables from menu, and jumping for first time
        let menu = true;
        let firstJump = true;
        let justDied = true;
        let dead = false;

        let flapFrame = 0;

        //pigeon coords, and gravity
        let bird;
        let birdX = (window.innerWidth/2 - 25);
        let birdY = window.innerHeight/2;
        let birdG = 6;

        //item list
        let randomItem;
        let randomY;
        let itemCountdown = 0;
        let itemRate = 200;
        let itemSpeed = 5;
        let itemScale = 1;
        let rotationSpeed = 3;
        let itemPictureList = [beanCanImage, beerBottleImage, brokenMugImage, cardboardBoxImage, drinkCanImage, drinkCupImage, fishCanImage, glassBottleImage, glassJarImage,
        milkCartonImage, newsPapersImage, paperBagImage, pizzaBoxImage, plasticBottleImage, sprayCanImage, washingStuffImage];
        let itemList = [];

        let deathItem = 1000;

        //scoreboard variables
        let score = 0;


        //redoing canvas and menu text when resize happens
        function mainPageResize(){
            canvas.width = (window.innerWidth-30);
            canvas.height = (window.innerHeight-30);
            birdX = (window.innerWidth/2 - 25);

            let set = "<?php echo"$cookieExists"?>";

            if(menu && set==false){
                document.getElementById("leaderboard").style.display = "block";

                ctx.fillStyle = "#FFED29";

                if(window.innerWidth<600){
                    ctx.font = '50px "Bungee"';
                    ctx.shadowOffsetX = 5;
                    ctx.shadowOffsetY = 2.5;
                }else if(window.innerWidth<1280){
                    ctx.font = '60px "Bungee"';
                    ctx.shadowOffsetX = 7.5;
                    ctx.shadowOffsetY = 3.5;
                } else{
                    ctx.font = '90px "Bungee"';
                    ctx.shadowOffsetX = 10;
                    ctx.shadowOffsetY = 5;
                }

                ctx.shadowColor = "black";

                ctx.fillText(gameName,(window.innerWidth/2 - (ctx.measureText(gameName).width/2)),window.innerHeight/3.5);
            } else if(menu && set==true){
                username = "<?php echo $userName?>";
                const nameContainer = document.getElementById("usernameContainer");
                const errorText = document.getElementById("errorText");
                errorText.innerHTML = "";
                menu = false;
                nameContainer.style.display = "none";
                mainPageResize();

                mainGame();
            }
        }


        //username checks when submit button clicked, if all good open game
        document.getElementById("submitName").addEventListener("click", () => {
            username = document.getElementById("nameInput").value;
            const nameContainer = document.getElementById("usernameContainer");
            const errorText = document.getElementById("errorText");
            if(username.trim() === ""){
                errorText.innerHTML = "empty username entered";
            } else if(username.length < 3){
                errorText.innerHTML = "username must be at least 3 characters long";
            }else{
                document.cookie = "username=" + username;
                errorText.innerHTML = "";
                menu = false;
                nameContainer.style.display = "none";
                mainPageResize();

                mainGame();
            }
        });

        //if play again button clicked
        document.getElementById("resetButton").addEventListener("click", () =>{
            location.reload();
        });

        //if main menu button clicked
        document.getElementById("menuButton").addEventListener("click", () =>{
            document.cookie = "menuCookie=true";
            location.reload();
        });

        //class for the thrown objects
        class thrownItem{
            constructor(link, iX, iY, rotation = 360){
                this.link = link;
                this.iX = iX;
                this.iY = iY
                this.rotation = rotation;
            }

            drawItem(){
                ctx.save();
                ctx.translate(this.iX, this.iY);
                ctx.rotate(this.rotation *Math.PI/180); // change degrees to radians
                ctx.scale(itemScale,itemScale);
                ctx.drawImage(this.link, -this.link.width/2, -this.link.height/2);
                ctx.restore();
            }

            moveLeft(){
                this.iX-=itemSpeed;

                // collision code inspired by: https://stackoverflow.com/questions/13916966/adding-collision-detection-to-images-drawn-on-canvas
                if(this.iX < birdX + pigeonImage.naturalWidth/4 && this.iX + this.link.naturalWidth*itemScale > birdX && this.iY < birdY + pigeonImage.naturalHeight && this.iY + this.link.naturalHeight*itemScale  > birdY){
                    dead = true;
                    ctx.save()
                    ctx.translate(birdX,birdY);
                    ctx.rotate(180 *Math.PI/180);
                    ctx.drawImage(pigeonImage, sheetX, sheetY, sheetW, sheetH, -pigeonImage.width/2, -pigeonImage.height, birdScale, birdScale);
                    ctx.restore();
                }
            }

            rotateItem(){
                this.rotation = (this.rotation - rotationSpeed) % 360;
            }

            fall(){
                this.iY+=5;
            }
        }

        //actual game start
        function mainGame(){
            document.getElementById("leaderboard").style.display = "none";

            //clear the canvas
            ctx.clearRect(0,0,canvas.width,canvas.height);

            //scrolling background code based off https://www.geeksforgeeks.org/html5-game-development-infinitely-scrolling-background/
            ctx.drawImage(scrollBG1, bgWidth1 , 0);
            ctx.drawImage(scrollBG1, bgWidth1 + 2304 , 0);
            bgWidth1 -= scrollSpeed/5;
            if(bgWidth1 <= -2304){
                bgWidth1 = 0;
            }

            ctx.drawImage(scrollBG2, bgWidth2 , 0);
            ctx.drawImage(scrollBG2, bgWidth2 + 2304 , 0);
            bgWidth2 -= scrollSpeed;
            if(bgWidth2 <= -2304){
                bgWidth2 = 0;
            }

            ctx.drawImage(scrollBG3, bgWidth3 , 0);
            ctx.drawImage(scrollBG3, bgWidth3 + 2304 , 0);
            bgWidth3 -= scrollSpeed*2;
            if(bgWidth3 <= -2304){
                bgWidth3 = 0;
            }

            ctx.drawImage(scrollBG4, bgWidth4 , 0);
            ctx.drawImage(scrollBG4, bgWidth4 + 2304 , 0);
            bgWidth4 -= scrollSpeed*3;
            if(bgWidth4 <= -2304){
                bgWidth4 = 0;
            }

            ctx.drawImage(scrollBG5, bgWidth5 , 0);
            ctx.drawImage(scrollBG5, bgWidth5 + 2304 , 0);
            bgWidth5 -= scrollSpeed*4;
            if(bgWidth5 <= -2304){
                bgWidth5 = 0;
            }

            //adds points text to top left, then checks if user has jumped before so pigeon doesn't instantly fall, and add press space to start text
            ctx.fillStyle = "#FFED29";

            if(window.innerWidth<600){
                ctx.font = '20px "Bungee"';
                ctx.shadowOffsetX = 5;
                ctx.shadowOffsetY = 2.5;
            }else if(window.innerWidth<1280){
                ctx.font = '20px "Bungee"';
                ctx.shadowOffsetX = 7.5;
                ctx.shadowOffsetY = 3.5;
            } else{
                ctx.font = '30px "Bungee"';
                ctx.shadowOffsetX = 10;
                ctx.shadowOffsetY = 5;
            }

            ctx.shadowColor = "black";
            ctx.fillText("Score: "+ Math.round(score), 50, 50);
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;

            if((!firstJump && birdY<=window.innerHeight-130)|| dead){
                birdY+=birdG;

                if(!dead){
                    score+=0.1;
                }

            } else if(firstJump){
                ctx.fillStyle = "#FFED29";

                if(window.innerWidth<600){
                    ctx.font = '30px "Bungee"';
                    ctx.shadowOffsetX = 5;
                    ctx.shadowOffsetY = 2.5;
                }else if(window.innerWidth<1280){
                    ctx.font = '40px "Bungee"';
                    ctx.shadowOffsetX = 7.5;
                    ctx.shadowOffsetY = 3.5;
                } else{
                    ctx.font = '70px "Bungee"';
                    ctx.shadowOffsetX = 10;
                    ctx.shadowOffsetY = 5;
                }

                ctx.shadowColor = "black";
                ctx.fillText("Press space to start",(window.innerWidth/2 - (ctx.measureText("Press space to start").width/2)),window.innerHeight/3.5);
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = 0;
            }

            //animation loop for pigeon
            if(loopCount == 5 && !dead){
                if(sheetX == 64*1){
                    lastFrame = 64*1;
                    sheetX = 64*0;
                } else if(sheetX == 64*0 && lastFrame == 64*1){
                    lastFrame = 64*0;
                    sheetX = 64*2;
                } else if(sheetX == 64*2){
                    lastFrame = 64*2;
                    sheetX = 64*1;
                }
                loopCount = 0;
            } else if(!dead){
                loopCount+=1;
            }

            //draw new items and change their position/rotation

            if(!firstJump && itemCountdown==0){
                randomItem = itemPictureList[Math.floor(Math.random() * itemPictureList.length)]; // used https://www.geeksforgeeks.org/how-to-select-a-random-element-from-array-in-javascript/
                randomY = Math.floor(Math.random() * (canvas.height-50));
                rotation = Math.random() * 360;
                itemList.push(new thrownItem(randomItem, canvas.width, randomY));
                itemCountdown = itemRate;
            } else if(!firstJump){
                itemCountdown-=1;
            }

            for(let i=0; i<itemList.length; i++){
                itemList[i].rotateItem();
                itemList[i].drawItem();
                itemList[i].moveLeft();
                if(dead){
                    if(deathItem == 1000){
                        deathItem = i;
                    } else if(deathItem == i){
                        itemList[i].fall();
                    }
                }
            }

            //draw pete (the pigeon)
            if(!dead){
                ctx.drawImage(pigeonImage, sheetX, sheetY, sheetW, sheetH, (birdX - sheetW/8), (birdY - sheetH/8), birdScale, birdScale);
            } else{
                //fall when dead
                if(birdY<=window.innerHeight+200){
                    birdY+=2;
                    birdX+=2;
                    sheetX = 64*3;
                    ctx.save()
                    ctx.translate(birdX,birdY);
                    ctx.rotate(180 *Math.PI/180);
                    ctx.drawImage(pigeonImage, sheetX, sheetY, sheetW, sheetH, -pigeonImage.width/2, -pigeonImage.height, birdScale, birdScale);
                    ctx.restore();
                } else{
                    //gameover screen after death
                    ctx.fillStyle = "#FF0000";

                    //makes sure player is only added once to database after death
                    if(justDied){
                        console.log("ran once");
                        addScore();
                    }
                    justDied = false;

                    if(window.innerWidth<600){
                        ctx.font = '30px "Bungee"';
                        ctx.shadowOffsetX = 5;
                        ctx.shadowOffsetY = 2.5;
                    }else if(window.innerWidth<1280){
                        ctx.font = '40px "Bungee"';
                        ctx.shadowOffsetX = 7.5;
                        ctx.shadowOffsetY = 3.5;
                    } else{
                        ctx.font = '70px "Bungee"';
                        ctx.shadowOffsetX = 10;
                        ctx.shadowOffsetY = 5;
                    }
                    ctx.shadowColor = "black";
                    ctx.fillText("Game Over",(window.innerWidth/2 - (ctx.measureText("Game Over").width/2)),window.innerHeight/3.5);


                    //adding their final score to the screen
                    if(window.innerWidth<600){
                        ctx.font = '20px "Bungee"';
                        ctx.shadowOffsetX = 5;
                        ctx.shadowOffsetY = 2.5;
                    }else if(window.innerWidth<1280){
                        ctx.font = '30px "Bungee"';
                        ctx.shadowOffsetX = 7.5;
                        ctx.shadowOffsetY = 3.5;
                    } else{
                        ctx.font = '50px "Bungee"';
                        ctx.shadowOffsetX = 10;
                        ctx.shadowOffsetY = 5;
                    }

                    if(username.endsWith("s")){
                        ctx.fillText(username +"' final Score: "+ Math.round(score),(window.innerWidth/2 - (ctx.measureText(username +"' final Score: "+ Math.round(score)).width/2)),window.innerHeight/2.5);
                    } else{
                        ctx.fillText(username +"'s final Score: "+ Math.round(score),(window.innerWidth/2 - (ctx.measureText(username +"'s final Score: "+ Math.round(score)).width/2)),window.innerHeight/2.5);
                    }

                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;

                    //button to reset game
                    resetContainer = document.getElementById("resetContainer");
                    resetContainer.style.display = "flex";

                }
            }

            requestAnimationFrame(mainGame);
        }

        //adding score to database
        async function addScore() {
            const url = `save_score.php?username=${username}&score=${Math.round(score)}`;

            try {
                await fetch(url);
            } catch (error) {
                console.error("Error saving score:", error);
            }
        }

        //smooth movement for bird instead of teleporting
        function flap(){
            if(flapFrame!==50 && !dead){
                flapFrame+=1;
                birdY-=4;
                requestAnimationFrame(flap);
            } else{
                flapFrame = 0;
            }
        }

        //changing speed/amount of items over time
        function itemsOverTime(){
            if(!firstJump){
                itemSpeed+=0.5;
                rotationSpeed+=0.5;
                if (itemRate>40){
                    itemRate-=40;
                }
            }

        }


        setInterval(itemsOverTime, 5000);


        //check if space button pressed and change birdY value
        document.addEventListener("keyup", event => {
            if(event.code === "Space" && birdY>100){
                firstJump = false;
                flap();
            }
        })

        //when user resizes page, canvas resizes as well
        window.addEventListener("resize", mainPageResize);

        //waits for my images to load before running
        window.onload = function(){
            document.fonts.ready.then(() => {
                document.getElementsByTagName("body")[0].style.display = "block";
                mainPageResize();
            });

        }
    </script>
</body>
</html>