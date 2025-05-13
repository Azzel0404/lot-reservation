<?php
include('../../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cebu address database (same as in create_lot.php)
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

if (isset($_POST['update_lot'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = $_POST['lot_number']; // This is now read-only in the form
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

    // Prepare update query parts
    $sql_parts = [
        "location = '$location'",
        "size_meter_square = $size",
        "price = $price",
        "status = '$status'"
    ];

    // Handle aerial image upload
    if (!empty($_FILES['aerial_image']['name'])) {
        // Validate image type
        if (!in_array($_FILES['aerial_image']['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed for aerial image.";
            header("Location: lots.php");
            exit();
        }

        $aerial_image_name = time() . '_' . basename($_FILES['aerial_image']['name']);
        $aerial_target = $uploadDir . $aerial_image_name;
        
        if (move_uploaded_file($_FILES['aerial_image']['tmp_name'], $aerial_target)) {
            $sql_parts[] = "aerial_image = '$aerial_image_name'";
            
            // Delete old aerial image if exists
            if (!empty($_POST['old_aerial_image'])) {
                $old_aerial = $uploadDir . $_POST['old_aerial_image'];
                if (file_exists($old_aerial)) {
                    unlink($old_aerial);
                }
            }
        } else {
            $_SESSION['error'] = "Failed to upload aerial image.";
            header("Location: lots.php");
            exit();
        }
    }

    // Handle numbered image upload
    if (!empty($_FILES['numbered_image']['name'])) {
        // Validate image type
        if (!in_array($_FILES['numbered_image']['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed for numbered image.";
            header("Location: lots.php");
            exit();
        }

        $numbered_image_name = time() . '_' . basename($_FILES['numbered_image']['name']);
        $numbered_target = $uploadDir . $numbered_image_name;
        
        if (move_uploaded_file($_FILES['numbered_image']['tmp_name'], $numbered_target)) {
            $sql_parts[] = "numbered_image = '$numbered_image_name'";
            
            // Delete old numbered image if exists
            if (!empty($_POST['old_numbered_image'])) {
                $old_numbered = $uploadDir . $_POST['old_numbered_image'];
                if (file_exists($old_numbered)) {
                    unlink($old_numbered);
                }
            }
        } else {
            // Clean up aerial image if it was uploaded
            if (isset($aerial_target) && file_exists($aerial_target)) {
                unlink($aerial_target);
            }
            $_SESSION['error'] = "Failed to upload numbered image.";
            header("Location: lots.php");
            exit();
        }
    }

    // Handle PDF file upload
    if (!empty($_FILES['pdf_file']['name'])) {
        if ($_FILES['pdf_file']['type'] !== 'application/pdf') {
            $_SESSION['error'] = "Uploaded file must be a PDF.";
            header("Location: lots.php");
            exit();
        }

        $pdf_file_name = time() . '_' . basename($_FILES['pdf_file']['name']);
        $pdf_target = $uploadDir . $pdf_file_name;
        
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_target)) {
            $sql_parts[] = "pdf_file = '$pdf_file_name'";
            
            // Delete old PDF if exists
            if (!empty($_POST['old_pdf_file'])) {
                $old_pdf = $uploadDir . $_POST['old_pdf_file'];
                if (file_exists($old_pdf)) {
                    unlink($old_pdf);
                }
            }
        } else {
            // Clean up other uploaded files if they exist
            if (isset($aerial_target) && file_exists($aerial_target)) {
                unlink($aerial_target);
            }
            if (isset($numbered_target) && file_exists($numbered_target)) {
                unlink($numbered_target);
            }
            $_SESSION['error'] = "Failed to upload PDF file.";
            header("Location: lots.php");
            exit();
        }
    }

    // Combine all parts and run query
    $sql = "UPDATE lot SET " . implode(', ', $sql_parts) . " WHERE lot_id = $lot_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lot updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed: " . mysqli_error($conn);
        
        // Clean up any uploaded files if the DB update failed
        if (isset($aerial_target) && file_exists($aerial_target)) {
            unlink($aerial_target);
        }
        if (isset($numbered_target) && file_exists($numbered_target)) {
            unlink($numbered_target);
        }
        if (isset($pdf_target) && file_exists($pdf_target)) {
            unlink($pdf_target);
        }
    }

    header("Location: lots.php");
    exit();
}

// If not a POST request, show the edit form
$lot_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lot = null;

if ($lot_id > 0) {
    $query = $conn->query("SELECT * FROM lot WHERE lot_id = $lot_id");
    if ($query && $query->num_rows > 0) {
        $lot = $query->fetch_assoc();
    } else {
        $_SESSION['error'] = "Lot not found.";
        header("Location: lots.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid lot ID.";
    header("Location: lots.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lot</title>
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
        .readonly-input {
            background-color: #f5f5f5;
            cursor: not-allowed;
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
        .error {
            color: #e74c3c;
            margin-top: 5px;
        }
        .success {
            color: #27ae60;
            margin-top: 5px;
        }
        .current-files {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Lot</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="lot_id" value="<?= htmlspecialchars($lot['lot_id']) ?>">
            <input type="hidden" name="old_aerial_image" value="<?= htmlspecialchars($lot['aerial_image'] ?? '') ?>">
            <input type="hidden" name="old_numbered_image" value="<?= htmlspecialchars($lot['numbered_image'] ?? '') ?>">
            <input type="hidden" name="old_pdf_file" value="<?= htmlspecialchars($lot['pdf_file'] ?? '') ?>">
            
            <div class="form-group">
                <label for="lot_number">Lot Number</label>
                <input type="text" name="lot_number" id="lot_number" value="<?= htmlspecialchars($lot['lot_number']) ?>" class="readonly-input" readonly>
                <small>Standard Format: LOT-YYMM-XXX (Cannot be changed)</small>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <div class="autocomplete">
                    <input type="text" name="location" id="location" value="<?= htmlspecialchars($lot['location']) ?>" placeholder="Start typing city or barangay..." required>
                    <div id="autocomplete-results" class="autocomplete-items"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="size_meter_square">Size (m²)</label>
                <input type="number" step="0.01" name="size_meter_square" id="size_meter_square" value="<?= htmlspecialchars($lot['size_meter_square']) ?>" placeholder="Size in m²" required min="0.01">
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" value="<?= htmlspecialchars($lot['price']) ?>" placeholder="Price" required min="0">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="Available" <?= $lot['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Reserved" <?= $lot['status'] === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                    <option value="Sold" <?= $lot['status'] === 'Sold' ? 'selected' : '' ?>>Sold</option>
                </select>
            </div>

            <div class="form-group">
                <label for="aerial_image">Aerial Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="aerial_image" id="aerial_image" accept="image/jpeg,image/png">
                <?php if (!empty($lot['aerial_image'])): ?>
                    <div class="current-files">
                        Current: <?= htmlspecialchars($lot['aerial_image']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="numbered_image">Numbered Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="numbered_image" id="numbered_image" accept="image/jpeg,image/png">
                <?php if (!empty($lot['numbered_image'])): ?>
                    <div class="current-files">
                        Current: <?= htmlspecialchars($lot['numbered_image']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="pdf_file">PDF File (optional, max 10MB)</label>
                <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf">
                <?php if (!empty($lot['pdf_file'])): ?>
                    <div class="current-files">
                        Current: <?= htmlspecialchars($lot['pdf_file']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="update_lot">Update Lot</button>
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
            
            // Validate image files if they are being updated
            if (aerialImage.files.length) {
                const allowedImageTypes = ['image/jpeg', 'image/png'];
                if (!allowedImageTypes.includes(aerialImage.files[0].type)) {
                    alert('Only JPEG and PNG images are allowed for aerial image.');
                    return false;
                }
                
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (aerialImage.files[0].size > maxSize) {
                    alert('Aerial image must be smaller than 5MB.');
                    return false;
                }
            }
            
            if (numberedImage.files.length) {
                const allowedImageTypes = ['image/jpeg', 'image/png'];
                if (!allowedImageTypes.includes(numberedImage.files[0].type)) {
                    alert('Only JPEG and PNG images are allowed for numbered image.');
                    return false;
                }
                
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (numberedImage.files[0].size > maxSize) {
                    alert('Numbered image must be smaller than 5MB.');
                    return false;
                }
            }
            
            // Validate PDF file if being updated
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
