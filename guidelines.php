<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="guideStyle.css">
    <title>Guidlines</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap');
        @import url("https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400;1,700&display=swap");

        @font-face {
            font-family: 'Canva Sans';
            src: url('canva-sans.woff2') format('woff2');
        }

        /* Reset some default styles */
        body,
        ul,
        li {
            margin: 0;
            padding: 0;
            list-style: none;
        }



        .boxFP {
            height: 240px;
            width: 1278px;
            border: 1px solid #fff;
            background-image: url(Copy\ of\ Lost\ and\ Found.png);
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            font-family: 'Canva Sans', sans-serif;
            font-weight: bold;
            font-size: 50px;
            color: #fff;
            text-align: center;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);

            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .orangeBox {
            margin-top: 20px;
            height: 90px;
            width: 1278px;
            background-color: #f8d0b4;
        }

        .orangeBtxt {
            font-family: 'Canva Sans', sans-serif;
            font-size: 16px;
            text-align: justify;
            font-weight: normal;
            color: #726e6e;
            padding-top: 14px;
            margin-left: 60px;
            margin-right: 60px;
        }

        /*PARENT1*/
        .container2 {
            display: flex;
            /* Makes children (claimGuide and foundGuide) appear side by side */
            justify-content: space-between;
            /* Optional: space out the guides evenly */
            gap: 20px;
            /* Optional: Adds space between the two guides */
        }

        /*CLAIM GUIDE */
        .claimGuide {
            margin-top: 20px;
            margin-left: 40px;
            height: 350px;
            width: 600px;
            border: 1px solid white;
        }

        .claimh3 {
            font-family: 'Canva Sans', sans-serif;
            font-size: 18px;
            color: #726e6e;
            padding-left: 20px;
        }

        .claimGuide ul {
            padding-left: 40px;
            font-family: 'Canva Sans', sans-serif;
            font-size: 16px;
            color: #726e6e;
            text-align: justify;
        }

        .claimGuide ul li {
            margin-bottom: 10px;
            /* Optional: Adds space between list items */
            list-style-type: disc;
        }


        /*FOUND GUIDE */
        .foundGuide {
            margin-top: 20px;
            margin-left: 1px;
            margin-right: 50px;
            height: 350px;
            width: 600px;
            border: 1px solid white;
        }

        .foundh3 {
            font-family: 'Canva Sans', sans-serif;
            font-size: 18px;
            color: #726e6e;
            padding-left: 20px;
        }

        .foundGuide ul {
            padding-left: 40px;
            font-family: 'Canva Sans', sans-serif;
            font-size: 16px;
            color: #726e6e;
            text-align: justify;
        }

        .foundGuide ul li {
            margin-bottom: 10px;
            list-style-type: disc;
        }


        /*PARENT2*/
        .container3 {
            display: flex;
            /* Makes children (claimGuide and foundGuide) appear side by side */
            justify-content: space-between;
            /* Optional: space out the guides evenly */
            gap: 20px;
            /* Optional: Adds space between the two guides */
        }

        /*LOST GUIDE */
        .lostGuide {
            margin-top: -50px;
            margin-left: 1px;
            margin-right: 50px;
            height: 350px;
            width: 800px;
            border: 1px solid white;
        }

        .losth3 {
            font-family: 'Canva Sans', sans-serif;
            font-size: 18px;
            color: #726e6e;
            padding-left: 60px;
        }

        .lostGuide ul {
            padding-left: 80px;
            font-family: 'Canva Sans', sans-serif;
            font-size: 16px;
            color: #726e6e;
            text-align: justify;
        }

        .lostGuide ul li {
            margin-bottom: 10px;
            list-style-type: disc;
        }

        /*HELP GUIDE */
        .helpGuide {
            margin-top: -50px;
            margin-left: 1px;
            margin-right: 170px;
            height: 350px;
            width: 600px;
            border: 1px solid white;
        }

        .helph3 {
            font-family: 'Canva Sans', sans-serif;
            font-size: 18px;
            color: #726e6e;
            padding-left: -10px;
        }

        .helpGuide ul {
            padding-left: 38px;
            font-family: 'Canva Sans', sans-serif;
            font-size: 16px;
            color: #726e6e;
            text-align: justify;
        }

        .helpGuide ul li {
            margin-bottom: 1px;
        }
    </style>

<body>
    <nav class="navbar">
        <div class="navlogo"> <img src="starlogo.png" alt="Website Logo"> </div>
        <ul class="navpage">
            <li><a href="#home">Home</a></li>
            <li><a href="#guidelines">Guidelines</a></li>
            <li><a href="#browsereports">Browse Reports</a></li>
        </ul>
        <button class="loginB"> Log in </button>
    </nav>
    <div class="boxFP"> Guidelines and Procedures </div>

    <div class="orangeBox">
        <p class="orangeBtxt">
            We understand the importance of your belongings and are committed to helping you recover any lost items.
            Should you misplace any personal items during your visit, please report them promptly at the Lost and Found
            Counter located in the Main Lobby. If you cannot report in person, you can file a report and receive updates
            on your inquiry by visiting our Lost and Found Reporting Portal. Our team is dedicated to locating,
            safeguarding, and returning your items as quickly as possible.
        </p>
    </div>

    <div class="container2">
        <div class="claimGuide">
            <h3 class="claimh3"> GUIDELINES IN CLAIMING A LOST ITEM: </h3>
            <ul>
                <li> When visiting the Lost and Found, be prepared to verify your identity. Bring an ID, and if
                    possible, any documentation that proves your connection to the lost item (e.g., a purchase receipt,
                    a photo, or serial number). </li>
                <li> Describe the item in detail, including any unique features, brand, color, and specific details that
                    can confirm ownership. For high-value items, the staff may ask additional questions to verify that
                    it belongs to you.</li>
                <li> Some places may have a time limit for claiming lost items. Try to claim your item as soon as
                    possible to avoid potential disposal or donation if the item remains unclaimed for too long.</li>
            </ul>
        </div>

        <div class="foundGuide">
            <h3 class="foundh3"> FOUND ITEMS: </h3>
            <ul>
                <li> If you find an item, take it to the nearest Lost and Found location or inform a staff member right
                    away. Avoid keeping the item in your possession for an extended period, as this can delay its return
                    to the owner. </li>
                <li> Provide the exact location where the item was found, along with the date and time. This information
                    is important for tracking and helps the Lost and Found staff connect the item with any reports that
                    may come in. </li>
                <li> Some organizations or locations may have specific protocols for handling valuable items or items
                    with sensitive information (e.g., wallets, electronics). Be prepared to follow these as requested by
                    the Lost and Found staff. </li>
            </ul>
        </div>
    </div>

    <div class="container3">
        <div class="lostGuide">
            <h3 class="losth3"> LOST ITEMS: </h3>
            <ul>
                <li> Cuts, Scratches, Scuffs, Dents, Marks. </li>
                <li> Damage to or lost of protruding parts, including straps, pockets, handles, hooks, wheels, external
                    locks, security straps, or zipper tabs. </li>
                <li> Damage due to improper handling or storage. </li>
                <li> Items left unaattended or abandoned in public or restricted areas. </li>
            </ul>
        </div>

        <div class="helpGuide">
            <h3 class="helph3"> Got a question or need some help? Contact us at: </h3>
            <ul>
                <li> <b>Email:</b> support@lostandfoundhelp.com </li>
                <li> <b>Phone:</b> +1 (800) 555-LOST (5678) </li>
                <li> <b>Address:</b> 123 Discovery Lane, Finder's City, FL 56789 </li>
                <li> <b>Business Hours: </b> </li>
                <li> Monday–Friday: 9 AM–5 PM </li>
                <li> Saturday: 10 AM–3 PM</li>
                <li> Sunday: Closed </li>
            </ul>
        </div>
    </div>
</body>
</head>

</html>