<?php
include('../../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cebu address database
$cebuAddresses = [
   // Cebu City
"Cebu City - Adlaon",
"Cebu City - Apas",
"Cebu City - Bacayan",
"Cebu City - Banilad",
"Cebu City - Basak Pardo",
"Cebu City - Basak San Nicolas",
"Cebu City - Binaliw",
"Cebu City - Bonbon",
"Cebu City - Budlaan",
"Cebu City - Buhisan",
"Cebu City - Bulacao",
"Cebu City - Buot-Taup",
"Cebu City - Busay",
"Cebu City - Calamba",
"Cebu City - Cambinocot",
"Cebu City - Capitol Site",
"Cebu City - Carreta",
"Cebu City - Cogon Pardo",
"Cebu City - Cogon Ramos",
"Cebu City - Day-as",
"Cebu City - Duljo Fatima",
"Cebu City - Ermita",
"Cebu City - Guadalupe",
"Cebu City - Guba",
"Cebu City - Hipodromo",
"Cebu City - Inayawan",
"Cebu City - Kalubihan",
"Cebu City - Kamagayan",
"Cebu City - Kamputhaw",
"Cebu City - Kasambagan",
"Cebu City - Kinasang-an",
"Cebu City - Labangon",
"Cebu City - Lahug",
"Cebu City - Lorega San Miguel",
"Cebu City - Lusaran",
"Cebu City - Luz",
"Cebu City - Mabini",
"Cebu City - Mabolo",
"Cebu City - Malubog",
"Cebu City - Mambaling",
"Cebu City - Pahina Central",
"Cebu City - Pahina San Nicolas",
"Cebu City - Pamutan",
"Cebu City - Parian",
"Cebu City - Pari-an",
"Cebu City - Pasil",
"Cebu City - Pit-os",
"Cebu City - Pulangbato",
"Cebu City - Pung-ol Sibugay",
"Cebu City - Punta Princesa",
"Cebu City - Quiot",
"Cebu City - Sambag I",
"Cebu City - Sambag II",
"Cebu City - San Antonio",
"Cebu City - San Jose",
"Cebu City - San Nicolas Central",
"Cebu City - San Roque",
"Cebu City - Santa Cruz",
"Cebu City - Santo Niño",
"Cebu City - Sapangdaku",
"Cebu City - Sawang Calero",
"Cebu City - Sinsin",
"Cebu City - Sirao",
"Cebu City - Suba",
"Cebu City - Sudlon I",
"Cebu City - Sudlon II",
"Cebu City - T. Padilla",
"Cebu City - Tabunan",
"Cebu City - Tagba-o",
"Cebu City - Talamban",
"Cebu City - Taptap",
"Cebu City - Tejero",
"Cebu City - Tinago",
"Cebu City - Tisa",
"Cebu City - Toong",
"Cebu City - Zapatera",
            
// Minglanilla
"Minglanilla - Cadulawan",
"Minglanilla - Calajo-an",
"Minglanilla - Camp 7",
"Minglanilla - Camp 8",
"Minglanilla - Cuanos",
"Minglanilla - Guindaruhan",
"Minglanilla - Linao",
"Minglanilla - Manduang",
"Minglanilla - Pakigne",
"Minglanilla - Poblacion Ward I",
"Minglanilla - Poblacion Ward II",
"Minglanilla - Poblacion Ward III",
"Minglanilla - Poblacion Ward IV",
"Minglanilla - Tubod",
"Minglanilla - Tulay",
"Minglanilla - Tunghaan",
"Minglanilla - Tungkil",
"Minglanilla - Tungkop",
"Minglanilla - Vito",

//lapu lapu 
"Lapu-Lapu City - Agus",
"Lapu-Lapu City - Babag",
"Lapu-Lapu City - Bankal",
"Lapu-Lapu City - Baring",
"Lapu-Lapu City - Basak",
"Lapu-Lapu City - Buaya",
"Lapu-Lapu City - Calawisan",
"Lapu-Lapu City - Canjulao",
"Lapu-Lapu City - Caw-oy",
"Lapu-Lapu City - Cawhagan",
"Lapu-Lapu City - Caubian",
"Lapu-Lapu City - Gun-ob",
"Lapu-Lapu City - Ibo",
"Lapu-Lapu City - Looc",
"Lapu-Lapu City - Mactan",
"Lapu-Lapu City - Maribago",
"Lapu-Lapu City - Marigondon",
"Lapu-Lapu City - Pajac",
"Lapu-Lapu City - Pajo",
"Lapu-Lapu City - Pangan-an",
"Lapu-Lapu City - Poblacion",
"Lapu-Lapu City - Punta Engaño",
"Lapu-Lapu City - Pusok",
"Lapu-Lapu City - Sabang",
"Lapu-Lapu City - Santa Rosa",
"Lapu-Lapu City - Suba-basbas",
"Lapu-Lapu City - Talima",
"Lapu-Lapu City - Tingo",
"Lapu-Lapu City - Tungasan",
"Lapu-Lapu City - San Vicente",

// mandaue 
"Mandaue City - Alang-alang",
"Mandaue City - Bakilid",
"Mandaue City - Banilad",
"Mandaue City - Basak",
"Mandaue City - Cabancalan",
"Mandaue City - Cambaro",
"Mandaue City - Canduman",
"Mandaue City - Casili",
"Mandaue City - Casuntingan",
"Mandaue City - Centro",
"Mandaue City - Cubacub",
"Mandaue City - Guizo",
"Mandaue City - Ibabao-Estancia",
"Mandaue City - Jagobiao",
"Mandaue City - Labogon",
"Mandaue City - Looc",
"Mandaue City - Maguikay",
"Mandaue City - Mantuyong",
"Mandaue City - Opao",
"Mandaue City - Pakna-an",
"Mandaue City - Pagsabungan",
"Mandaue City - Subangdaku",
"Mandaue City - Tabok",
"Mandaue City - Tawason",
"Mandaue City - Tingub",
"Mandaue City - Tipolo",
"Mandaue City - Umapad",

//talisay
"Talisay City - Biasong",
"Talisay City - Bulacao",
"Talisay City - Candulawan",
"Talisay City - Camp IV",
"Talisay City - Cansojong",
"Talisay City - Dumlog",
"Talisay City - Jaclupan",
"Talisay City - Lagtang",
"Talisay City - Lawaan I",
"Talisay City - Lawaan II",
"Talisay City - Lawaan III",
"Talisay City - Linao",
"Talisay City - Maghaway",
"Talisay City - Manipis",
"Talisay City - Mohon",
"Talisay City - Poblacion",
"Talisay City - Pooc",
"Talisay City - San Isidro",
"Talisay City - San Roque",
"Talisay City - Tabunok",
"Talisay City - Tangke",
"Talisay City - Tapul",

//bogo city

"Bogo City - Anonang Norte",
"Bogo City - Anonang Sur",
"Bogo City - Banban",
"Bogo City - Binabag",
"Bogo City - Bongdo",
"Bogo City - Bongdo Gua",
"Bogo City - Cabungahan",
"Bogo City - Cagay",
"Bogo City - Cansaga",
"Bogo City - Cantagay",
"Bogo City - Cayang",
"Bogo City - Dakit",
"Bogo City - Don Pedro Rodriguez",
"Bogo City - Gairan",
"Bogo City - Guadalupe",
"Bogo City - La Purisima Concepcion",
"Bogo City - Lapaz",
"Bogo City - Malingin",
"Bogo City - Marangog",
"Bogo City - Nailon",
"Bogo City - Odlot",
"Bogo City - Pandan",
"Bogo City - Polambato",
"Bogo City - Sambag",
"Bogo City - San Vicente",
"Bogo City - Santo Niño",
"Bogo City - Santo Rosario",
"Bogo City - Siocon",
"Bogo City - Taytayan",

//carcar city

"Carcar City - Bolinawan",
"Carcar City - Buenavista",
"Carcar City - Calidngan",
"Carcar City - Can-asujan",
"Carcar City - Guadalupe",
"Carcar City - Liburon",
"Carcar City - Napo",
"Carcar City - Ocana",
"Carcar City - Perrelos",
"Carcar City - Poblacion I",
"Carcar City - Poblacion II",
"Carcar City - Poblacion III",
"Carcar City - Tuyom",
"Carcar City - Valencia",
"Carcar City - Valladolid",

//Danao City

"Danao City - Balingsag",
"Danao City - Bayabas",
"Danao City - Binaliw",
"Danao City - Cabungahan",
"Danao City - Cagat-Lamac",
"Danao City - Cahumayhumayan",
"Danao City - Cambanay",
"Danao City - Cambubho",
"Danao City - Cogon-Cruz",
"Danao City - Danasan",
"Danao City - Dungga",
"Danao City - Dunggoan",
"Danao City - Guinacot",
"Danao City - Guinsay",
"Danao City - Ibo",
"Danao City - Langosig",
"Danao City - Lawaan",
"Danao City - Licos",
"Danao City - Looc",
"Danao City - Magtagobtob",
"Danao City - Malapoc",
"Danao City - Manlayag",
"Danao City - Mantija",
"Danao City - Maslog",
"Danao City - Nangka",
"Danao City - Oguis",
"Danao City - Pili",
"Danao City - Poblacion",
"Danao City - Quisol",
"Danao City - Sabang",
"Danao City - Sacsac",
"Danao City - Sandayong Sur",
"Danao City - Santa Rosa",
"Danao City - Santican",
"Danao City - Sibacan",
"Danao City - Suba",
"Danao City - Taboc",
"Danao City - Taytay",
"Danao City - Togonon",
"Danao City - Tuburan Sur",
"Danao City - Tuburan Norte",
"Danao City - Dungguan",

//Naga City 

"Naga City - Alpaco",
"Naga City - Bairan",
"Naga City - Balirong",
"Naga City - Cabungahan",
"Naga City - Cantao-an",
"Naga City - Central Poblacion",
"Naga City - Cogon",
"Naga City - Colon",
"Naga City - East Poblacion",
"Naga City - Inoburan",
"Naga City - Inayagan",
"Naga City - Jaguimit",
"Naga City - Lanas",
"Naga City - Langtad",
"Naga City - Lutac",
"Naga City - Mainit",
"Naga City - Mayana",
"Naga City - Naalad",
"Naga City - North Poblacion",
"Naga City - Pangdan",
"Naga City - Patag",
"Naga City - South Poblacion",
"Naga City - Tagjaguimit",
"Naga City - Tangke",
"Naga City - Tinaan",
"Naga City - Tuyan",
"Naga City - Uling",
"Naga City - West Poblacion",

//toldeo City

"Toledo City - Awihao",
"Toledo City - Bagakay",
"Toledo City - Bato",
"Toledo City - Biga",
"Toledo City - Bulongan",
"Toledo City - Bunga",
"Toledo City - Cabitoonan",
"Toledo City - Calongcalong",
"Toledo City - Cantabaco",
"Toledo City - Captain Claudio",
"Toledo City - Carmen",
"Toledo City - Daanglungsod",
"Toledo City - Don Andres Soriano",
"Toledo City - Dumlog",
"Toledo City - DAS",
"Toledo City - General Climaco",
"Toledo City - Ibo",
"Toledo City - Landahan",
"Toledo City - Loay",
"Toledo City - Luray II",
"Toledo City - Matab-ang",
"Toledo City - Media Once",
"Toledo City - Pangamihan",
"Toledo City - Poblacion",
"Toledo City - Poog",
"Toledo City - Putingbato",
"Toledo City - Sagay",
"Toledo City - Sam-ang",
"Toledo City - Sangi",
"Toledo City - Santo Niño",
"Toledo City - Sirao",
"Toledo City - Subayon",
"Toledo City - Talavera",
"Toledo City - Tungay",
"Toledo City - Tubod",
"Toledo City - Tugbongan",
"Toledo City - Ulbong",
"Toledo City - Villahermosa",

//Consolacion

"Consolacion - Cabangahan",
"Consolacion - Cansaga",
"Consolacion - Casili",
"Consolacion - Danglag",
"Consolacion - Garing",
"Consolacion - Jugan",
"Consolacion - Lamac",
"Consolacion - Lanipga",
"Consolacion - Nangka",
"Consolacion - Panas",
"Consolacion - Panoypoy",
"Consolacion - Pitogo",
"Consolacion - Poblacion Occidental",
"Consolacion - Poblacion Oriental",
"Consolacion - Polog",
"Consolacion - Pulpogan",
"Consolacion - Sacsac",
"Consolacion - Tayud",
"Consolacion - Tilhaong",
"Consolacion - Tolotolo",
"Consolacion - Tugbongan",

//Cordova

"Cordova - Alegria",
"Cordova - Bangbang",
"Cordova - Buagsong",
"Cordova - Catarman",
"Cordova - Cogon",
"Cordova - Dapitan",
"Cordova - Day-as",
"Cordova - Gabi",
"Cordova - Gilutongan",
"Cordova - Ibabao",
"Cordova - Pilipog",
"Cordova - Poblacion",
"Cordova - San Miguel",

//liloan 

"Liloan - Cabadiangan",
"Liloan - Calero",
"Liloan - Catarman",
"Liloan - Cotcot",
"Liloan - Jubay",
"Liloan - Lataban",
"Liloan - Mulao",
"Liloan - Poblacion",
"Liloan - San Roque",
"Liloan - San Vicente",
"Liloan - Santa Cruz",
"Liloan - Tabla",
"Liloan - Tayud",
"Liloan - Yati",

//Compostela

"Compostela - Bagalnga",
"Compostela - Basak",
"Compostela - Buluang",
"Compostela - Cabadiangan",
"Compostela - Cambayog",
"Compostela - Canamucan",
"Compostela - Cogon",
"Compostela - Dapdap",
"Compostela - Estaca",
"Compostela - Lagundi",
"Compostela - Mulao",
"Compostela - Panangban",
"Compostela - Poblacion",
"Compostela - Tag-ube",
"Compostela - Tamiao",
"Compostela - Tubigan",
"Compostela - Tubod",

//balamban 
"Balamban - Abucayan",
"Balamban - Aliwanay",
"Balamban - Arpili",
"Balamban - Bayong",
"Balamban - Buanoy",
"Balamban - Cabagdalan",
"Balamban - Cabasiangan",
"Balamban - Cambuhawe",
"Balamban - Cansomoroy",
"Balamban - Cantibas",
"Balamban - Cantuod",
"Balamban - Duangan",
"Balamban - Gaas",
"Balamban - Ginatilan",
"Balamban - Hingatmonan",
"Balamban - Lamesa",
"Balamban - Liki",
"Balamban - Luca",
"Balamban - Matun-og",
"Balamban - Nangka",
"Balamban - Pondol",
"Balamban - Prenza",
"Balamban - Singsing",
"Balamban - Sunog",
"Balamban - Vito",
"Balamban - Santa Cruz-Santo Niño",
"Balamban - Santa Cruz-San Isidro",
"Balamban - Poblacion",

//Bantayan 

"Bantayan - Atop-atop",
"Bantayan - Baigad",
"Bantayan - Baod",
"Bantayan - Binaobao",
"Bantayan - Botigues",
"Bantayan - Kabac",
"Bantayan - Doong",
"Bantayan - Hilotongan",
"Bantayan - Guiwanon",
"Bantayan - Kabangbang",
"Bantayan - Kampingganon",
"Bantayan - Kangkaibe",
"Bantayan - Lipayran",
"Bantayan - Luyongbaybay",
"Bantayan - Mojon",
"Bantayan - Obo-ob",
"Bantayan - Patao",
"Bantayan - Putian",
"Bantayan - Sillon",
"Bantayan - Sungko",
"Bantayan - Suba",
"Bantayan - Sulangan",
"Bantayan - Tamiao",
"Bantayan - Poblacion",
"Bantayan - Ticad",

// Daanbantayan

"Daanbantayan - Agujo",
"Daanbantayan - Bagay",
"Daanbantayan - Bakhawan",
"Daanbantayan - Bateria",
"Daanbantayan - Bitoon",
"Daanbantayan - Calape",
"Daanbantayan - Carnaza",
"Daanbantayan - Dalingding",
"Daanbantayan - Lanao",
"Daanbantayan - Logon",
"Daanbantayan - Malbago",
"Daanbantayan - Malingin",
"Daanbantayan - Maya",
"Daanbantayan - Pajo",
"Daanbantayan - Paypay",
"Daanbantayan - Poblacion",
"Daanbantayan - Talisay",
"Daanbantayan - Tapilon",
"Daanbantayan - Tinubdan",
"Daanbantayan - Tominjao",

// Madridejos

"Madridejos - Bunakan",
"Madridejos - Kangwayan",
"Madridejos - Kaongkod",
"Madridejos - Kodia",
"Madridejos - Maalat",
"Madridejos - Malbago",
"Madridejos - Mancilang",
"Madridejos - Pili",
"Madridejos - Poblacion",
"Madridejos - San Agustin",
"Madridejos - Tabagak",
"Madridejos - Talangnan",
"Madridejos - Tarong",
"Madridejos - Tugas",

//San Fernando

"San Fernando - Balud",
"San Fernando - Balungag",
"San Fernando - Basak",
"San Fernando - Bugho",
"San Fernando - Cabatbatan",
"San Fernando - Greenhills",
"San Fernando - Ilaya",
"San Fernando - Linao",
"San Fernando - Panadtaran",
"San Fernando - Pitalo",
"San Fernando - Poblacion North",
"San Fernando - Poblacion South",
"San Fernando - Sangat",
"San Fernando - Tabionan",
"San Fernando - Tananas",
"San Fernando - Tubod",
"San Fernando - Tubod-Bitoon",
"San Fernando - Magsico",
"San Fernando - Pitogo",
"San Fernando - South Poblacion",
"San Fernando - Tonggo",

//Argao

"Argao - Alambijud",
"Argao - Anajao",
"Argao - Apo",
"Argao - Balaas",
"Argao - Balisong",
"Argao - Binlod",
"Argao - Bogo",
"Argao - Butong",
"Argao - Bug-ot",
"Argao - Bulasa",
"Argao - Calagasan",
"Argao - Canbantug",
"Argao - Canbanua",
"Argao - Cansuje",
"Argao - Capio-an",
"Argao - Casay",
"Argao - Catang",
"Argao - Colawin",
"Argao - Conalum",
"Argao - Guiwanon",
"Argao - Gutlang",
"Argao - Jampang",
"Argao - Jomgao",
"Argao - Lamacan",
"Argao - Langtad",
"Argao - Langub",
"Argao - Lapay",
"Argao - Lengigon",
"Argao - Linut-od",
"Argao - Mabasa",
"Argao - Mandilikit",
"Argao - Mompeller",
"Argao - Panadtaran",
"Argao - Poblacion",
"Argao - Sua",
"Argao - Sumaguan",
"Argao - Tabayag",
"Argao - Talaga",
"Argao - Talaytay",
"Argao - Talo-ot",
"Argao - Tiguib",
"Argao - Tulang",
"Argao - Tulic",
"Argao - Ubaub",
"Argao - Usmad",

//Barili

"Barili - Azucena",
"Barili - Bagakay",
"Barili - Balao",
"Barili - Bolocboloc",
"Barili - Budbud",
"Barili - Bugtong Kawayan",
"Barili - Cabcaban",
"Barili - Campangga",
"Barili - Dakit",
"Barili - Giloctog",
"Barili - Guibuangan",
"Barili - Giwanon",
"Barili - Gunting",
"Barili - Hilasgasan",
"Barili - Japitan",
"Barili - Kangdolsam",
"Barili - Candugay",
"Barili - Luhod",
"Barili - Lupo",
"Barili - Luyo",
"Barili - Maghanoy",
"Barili - Maigang",
"Barili - Malolos",
"Barili - Mamampao",
"Barili - Mantayupan",
"Barili - Mayana",
"Barili - Minolos",
"Barili - Nabunturan",
"Barili - Nasipit",
"Barili - Pancil",
"Barili - Pangpang",
"Barili - Paril",
"Barili - Patupat",
"Barili - Poblacion",
"Barili - San Rafael",
"Barili - Santa Ana",
"Barili - Sayaw",
"Barili - Sogod",
"Barili - Tal-ot",
"Barili - Tubod",
"Barili - Vito",
"Barili - Pagsupan",

//Dumanjug

"Dumanjug - Balaygtiki",
"Dumanjug - Bitoon",
"Dumanjug - Bulak",
"Dumanjug - Calaboon",
"Dumanjug - Camboang",
"Dumanjug - Candabong",
"Dumanjug - Cogon",
"Dumanjug - Cotcoton",
"Dumanjug - Daantol",
"Dumanjug - Don Miguel",
"Dumanjug - Kabalaasnan",
"Dumanjug - Kabatbatan",
"Dumanjug - Kambanog",
"Dumanjug - Kang-actol",
"Dumanjug - Kanghalo",
"Dumanjug - Kanghumaod",
"Dumanjug - Kanguha",
"Dumanjug - Kantangkas",
"Dumanjug - Kanyuko",
"Dumanjug - Cawayan",
"Dumanjug - Lanao",
"Dumanjug - Lawaan",
"Dumanjug - Liong",
"Dumanjug - Manlapay",
"Dumanjug - Masa",
"Dumanjug - Matalao",
"Dumanjug - Paculob",
"Dumanjug - Panlaan",
"Dumanjug - Pawa",
"Dumanjug - Poblacion",
"Dumanjug - Tangil",
"Dumanjug - Tapon",
"Dumanjug - Tunga",
"Dumanjug - Ilaya",
"Dumanjug - Tubod-Bitoon",
"Dumanjug - Tubod-Dugoan",
"Dumanjug - Poblacion Looc",

//Ronda

"Ronda - Butong",
"Ronda - Can-abuhon",
"Ronda - Canduling",
"Ronda - Cansalonoy",
"Ronda - Cansayong",
"Ronda - Caputatan Norte",
"Ronda - Caputatan Sur",
"Ronda - Casay",
"Ronda - Caubayan",
"Ronda - Dugyan",
"Ronda - Libo-o",
"Ronda - Malalay",
"Ronda - Palanas",
"Ronda - Poblacion",
"Ronda - Santa Cruz",
"Ronda - Tupas",
"Ronda - Tuyom",
"Ronda - Vive",
"Ronda - Langin",
"Ronda - Langtad",

//Alcantara

"Alcantara - Cabadiangan",
"Alcantara - Cabil-isan",
"Alcantara - Candabong",
"Alcantara - Lawaan",
"Alcantara - Manga",
"Alcantara - Palanas",
"Alcantara - Poblacion",
"Alcantara - Polo",
"Alcantara - Salug",

//Aloguinsan

"Aloguinsan - Angilan",
"Aloguinsan - Bojo",
"Aloguinsan - Bonbon",
"Aloguinsan - Esperanza",
"Aloguinsan - Kandingan",
"Aloguinsan - Kantabogon",
"Aloguinsan - Kawasan",
"Aloguinsan - Olango",
"Aloguinsan - Poblacion",
"Aloguinsan - Punay",
"Aloguinsan - Rosario",
"Aloguinsan - Saksak",
"Aloguinsan - Tampa-an",
"Aloguinsan - Toyokon",
"Aloguinsan - Upling",

//Asturias

"Asturias - Agbanga",
"Asturias - Agtugop",
"Asturias - Bago",
"Asturias - Bairan",
"Asturias - Banban",
"Asturias - Baye",
"Asturias - Bog-o",
"Asturias - Kaluangan",
"Asturias - Lanao",
"Asturias - Langub",
"Asturias - Looc Norte",
"Asturias - Looc Sur",
"Asturias - Lunas",
"Asturias - Magcalape",
"Asturias - Manguiao",
"Asturias - New Bago",
"Asturias - Owak",
"Asturias - Poblacion",
"Asturias - Saksak",
"Asturias - San Isidro",
"Asturias - San Roque",
"Asturias - Santa Lucia",
"Asturias - Santa Rita",
"Asturias - Tag-amakan",
"Asturias - Tagbubonga",
"Asturias - Tubigagmanok",
"Asturias - Tubod",

//Badian

"Badian - Alawijao",
"Badian - Balhaan",
"Badian - Banhigan",
"Badian - Basak",
"Badian - Basiao",
"Badian - Bato",
"Badian - Bugas",
"Badian - Calangcang",
"Badian - Candiis",
"Badian - Dagatan",
"Badian - Dobdob",
"Badian - Ginablan",
"Badian - Lambug",
"Badian - Malabago",
"Badian - Malhiao",
"Badian - Manduyong",
"Badian - Matutinao",
"Badian - Patong",
"Badian - Poblacion",
"Badian - Sanlagan",
"Badian - Santa Cruz",
"Badian - Sohoton",
"Badian - Talo-ot",
"Badian - Tanghas",
"Badian - Taytay",
"Badian - Tigbao",
"Badian - Tiguib",
"Badian - Tubod",
"Badian - Zaragosa",
];

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

// Function to generate the next available lot number
function getNextLotNumber($conn) {
    $query = $conn->query("SELECT MAX(lot_number) as max_lot FROM lot WHERE lot_number REGEXP '^LOT-[0-9]{4}-[0-9]{3}$'");
    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
        if (!empty($row['max_lot'])) {
            $parts = explode('-', $row['max_lot']);
            $sequence = intval($parts[2]) + 1;
            return $parts[0] . '-' . $parts[1] . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }
    }
    return "LOT-" . date("ym") . "-001";
}

$nextLotNumber = getNextLotNumber($conn);

// Fetch existing lot numbers from DB and validate format
$lot_numbers = [];
$lotQuery = $conn->query("SELECT lot_number, status FROM lot ORDER BY lot_number");
if ($lotQuery && $lotQuery->num_rows > 0) {
    while ($row = $lotQuery->fetch_assoc()) {
        if (preg_match('/^LOT-\d{4}-\d{3}$/', $row['lot_number'])) {
            $lot_numbers[$row['lot_number']] = $row['status'];
        }
    }
}

if (isset($_POST['submit'])) {
    // Automatically generate the lot number
    $lot_number = getNextLotNumber($conn);
    $location = trim($_POST['location']);
    $size = floatval($_POST['size_meter_square']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];

    // Validate price is not negative
    if ($price < 0) {
        $_SESSION['error'] = "Price cannot be negative.";
        header("Location: lots.php");
        exit();
    }

    // Validate size is positive
    if ($size <= 0) {
        $_SESSION['error'] = "Size must be greater than zero.";
        header("Location: lots.php");
        exit();
    }

    // Validate files were uploaded
    if (!isset($_FILES['aerial_image']) || !isset($_FILES['numbered_image'])) {
        $_SESSION['error'] = "Both image files are required.";
        header("Location: lots.php");
        exit();
    }

    $aerial_image = $_FILES['aerial_image'];
    $numbered_image = $_FILES['numbered_image'];

    // Check image types
    if (!in_array($aerial_image['type'], $allowed_types) || !in_array($numbered_image['type'], $allowed_types)) {
        $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed.";
        header("Location: lots.php");
        exit();
    }

    // Check image size (max 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($aerial_image['size'] > $maxFileSize || $numbered_image['size'] > $maxFileSize) {
        $_SESSION['error'] = "Image files must be less than 5MB.";
        header("Location: lots.php");
        exit();
    }

    // Move image files
    $aerial_image_name = time() . '_' . basename($aerial_image['name']);
    $aerial_path = $uploadDir . $aerial_image_name;
    if (!move_uploaded_file($aerial_image['tmp_name'], $aerial_path)) {
        $_SESSION['error'] = "Failed to upload aerial image.";
        header("Location: lots.php");
        exit();
    }

    $numbered_image_name = time() . '_' . basename($numbered_image['name']);
    $numbered_path = $uploadDir . $numbered_image_name;
    if (!move_uploaded_file($numbered_image['tmp_name'], $numbered_path)) {
        unlink($aerial_path);
        $_SESSION['error'] = "Failed to upload numbered image.";
        header("Location: lots.php");
        exit();
    }

    // Handle optional PDF file
    $pdf_file_name = null;
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdf_file = $_FILES['pdf_file'];
        if ($pdf_file['type'] === 'application/pdf') {
            if ($pdf_file['size'] > 10 * 1024 * 1024) {
                $_SESSION['error'] = "PDF file must be less than 10MB.";
                header("Location: lots.php");
                exit();
            }
            
            $pdf_file_name = time() . '_' . basename($pdf_file['name']);
            $pdf_path = $uploadDir . $pdf_file_name;
            if (!move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
                $_SESSION['error'] = "Failed to upload PDF file.";
                header("Location: lots.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Uploaded file must be a PDF.";
            header("Location: lots.php");
            exit();
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image, pdf_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddssss", $lot_number, $location, $size, $price, $status, $aerial_image_name, $numbered_image_name, $pdf_file_name);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Lot created successfully with number: " . $lot_number;
        header("Location: lots.php");
        exit();
    } else {
        unlink($aerial_path);
        unlink($numbered_path);
        if ($pdf_file_name) {
            unlink($uploadDir . $pdf_file_name);
        }
        $_SESSION['error'] = "Error creating lot: " . $stmt->error;
        header("Location: lots.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Lot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #2980b9;
        }
        small {
            color: #666;
            font-size: 0.9em;
        }
        .lot-number-section {
            background: #eaf2f8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            color: #e74c3c;
            margin-top: 5px;
        }
        .success {
            color: #27ae60;
            margin-top: 5px;
        }
        .format-display {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            border: 1px dashed #ccc;
            margin-top: 10px;
            font-family: monospace;
        }
        .lot-status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        .status-available {
            background-color: #2ecc71;
            color: white;
        }
        .status-reserved {
            background-color: #f39c12;
            color: white;
        }
        .status-sold {
            background-color: #e74c3c;
            color: white;
        }
        /* Autocomplete styles */
        .autocomplete {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            background-color: #007bff !important;
            color: #ffffff;
        }
        .existing-lots {
            margin-top: 10px;
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .existing-lot {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .existing-lot:last-child {
            border-bottom: none;
        }
        .generated-lot-number {
            font-weight: bold;
            font-size: 1.2em;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Lot</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="lot-number-section">
                <div class="form-group">
                    <label>Generated Lot Number</label>
                    <div class="generated-lot-number"><?= htmlspecialchars($nextLotNumber) ?></div>
                    <div class="format-display">
                        Standard Format: LOT-YYMM-XXX (Automatically generated)
                    </div>
                    
                    <?php if (!empty($lot_numbers)): ?>
                        <div class="existing-lots">
                            <strong>Existing Lot Numbers:</strong>
                            <?php foreach ($lot_numbers as $number => $status): ?>
                                <div class="existing-lot">
                                    <?= htmlspecialchars($number) ?>
                                    <span class="lot-status status-<?= strtolower($status) ?>"><?= $status ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <div class="autocomplete">
                    <input type="text" name="location" id="location" placeholder="Start typing city or barangay..." required>
                    <div id="autocomplete-results" class="autocomplete-items"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="size_meter_square">Size (m²)</label>
                <input type="number" step="0.01" name="size_meter_square" id="size_meter_square" placeholder="Size in m²" required min="0.01">
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" placeholder="Price" required min="0">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                </select>
            </div>

            <div class="form-group">
                <label for="aerial_image">Aerial Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="aerial_image" id="aerial_image" accept="image/jpeg,image/png" required>
            </div>

            <div class="form-group">
                <label for="numbered_image">Numbered Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="numbered_image" id="numbered_image" accept="image/jpeg,image/png" required>
            </div>

            <div class="form-group">
                <label for="pdf_file">PDF File (optional, max 10MB)</label>
                <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf">
            </div>

            <button type="submit" name="submit">Create Lot</button>
        </form>
    </div>

    <script>
        // Cebu address database
        const cebuAddresses = [
            <?php 
            foreach ($cebuAddresses as $address) {
                echo '"' . addslashes($address) . '",' . "\n";
            }
            ?>
        ];

        function autocomplete(inp, arr) {
            let currentFocus;
            inp.addEventListener("input", function(e) {
                const resultsDiv = document.getElementById("autocomplete-results");
                resultsDiv.innerHTML = '';
                const val = this.value.toLowerCase();
                
                if (!val) return false;
                
                currentFocus = -1;
                
                const matches = arr.filter(item => 
                    item.toLowerCase().includes(val)
                ).slice(0, 10); // Show max 10 results
                
                matches.forEach(match => {
                    const div = document.createElement("div");
                    div.innerHTML = "<strong>" + match.substring(0, val.length) + "</strong>";
                    div.innerHTML += match.substring(val.length);
                    div.innerHTML += "<input type='hidden' value='" + match + "'>";
                    div.addEventListener("click", function() {
                        inp.value = this.getElementsByTagName("input")[0].value;
                        resultsDiv.innerHTML = '';
                    });
                    resultsDiv.appendChild(div);
                });
            });
            
            inp.addEventListener("keydown", function(e) {
                let items = document.getElementById("autocomplete-results").children;
                if (e.keyCode === 40) { // Down arrow
                    currentFocus++;
                    addActive(items);
                } else if (e.keyCode === 38) { // Up arrow
                    currentFocus--;
                    addActive(items);
                } else if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    if (currentFocus > -1) {
                        items[currentFocus].click();
                    }
                }
            });
            
            function addActive(items) {
                if (!items) return false;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (items.length - 1);
                items[currentFocus].classList.add("autocomplete-active");
            }
            
            function removeActive(items) {
                Array.from(items).forEach(item => {
                    item.classList.remove("autocomplete-active");
                });
            }
            
            // Close the autocomplete when clicking elsewhere
            document.addEventListener("click", function(e) {
                if (e.target !== inp) {
                    document.getElementById("autocomplete-results").innerHTML = '';
                }
            });
        }

        // Initialize autocomplete when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            autocomplete(document.getElementById("location"), cebuAddresses);
        });

        function validateForm() {
            const price = document.getElementById('price');
            const size = document.getElementById('size_meter_square');
            const aerialImage = document.getElementById('aerial_image');
            const numberedImage = document.getElementById('numbered_image');
            
            if (price.value < 0) {
                alert('Price cannot be negative.');
                return false;
            }
            
            if (size.value <= 0) {
                alert('Size must be greater than zero.');
                return false;
            }
            
            if (!aerialImage.files.length || !numberedImage.files.length) {
                alert('Both image files are required.');
                return false;
            }
            
            const allowedImageTypes = ['image/jpeg', 'image/png'];
            if (!allowedImageTypes.includes(aerialImage.files[0].type) || 
                !allowedImageTypes.includes(numberedImage.files[0].type)) {
                alert('Only JPEG and PNG images are allowed.');
                return false;
            }
            
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (aerialImage.files[0].size > maxSize || numberedImage.files[0].size > maxSize) {
                alert('Image files must be smaller than 5MB.');
                return false;
            }
            
            const pdfFile = document.getElementById('pdf_file');
            if (pdfFile.files.length) {
                if (pdfFile.files[0].type !== 'application/pdf') {
                    alert('Uploaded file must be a PDF.');
                    return false;
                }
                if (pdfFile.files[0].size > 10 * 1024 * 1024) {
                    alert('PDF file must be smaller than 10MB.');
                    return false;
                }
            }
            
            return true;
        }
    </script>
</body>
</html>
