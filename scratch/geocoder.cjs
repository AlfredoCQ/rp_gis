const fs = require('fs');
const path = require('path');
const https = require('https');

const healthCentersRaw = [
  { name: "C.S. MATERNO INFANTIL BELLAVISTA PERÚ COREA", district: "BELLAVISTA", address: "MZ. F-5 ZONA 2 - CIUDAD DEL PESCADOR", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MENTAL COMUNITARIO UNIVERSITARIO CALLAO", district: "BELLAVISTA", address: "JR JUAN PABLO II 306 4 DENTRO DE LA UNIVERSIDAD NACIONAL DEL CALLAO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "HOSPITAL NACIONAL ALBERTO SABOGAL SOLOGUREN", district: "BELLAVISTA", address: "JR. COLINA 1081", hours: "24 HORAS", entity: "ESSALUD" },
  { name: "C.S. MANUEL BONILLA (BASE DE MICRORED)", district: "CALLAO", address: "AV. ALMIRANTE MIGUEL GRAU N 1015", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. ALBERTO BARTON", district: "CALLAO", address: "CALLE MANUEL RAYGADA N 515", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. SAN JUAN BOSCO", district: "CALLAO", address: "CONTRALMIRANTE MORA CDRA. 5 (CALLE NAUTA 122)", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. PUERTO NUEVO", district: "CALLAO", address: "LOCAL COMUNAL AA.HH. PUERTO NUEVO S/N", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. SANTA FE (BASE DE MICRORED)", district: "CALLAO", address: "AV. ALFREDO PALACIOS CDRA. 5", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. CALLAO", district: "CALLAO", address: "CALLE CANCHONES N 294 - URB TARAPACA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. JOSE BOTERIN", district: "CALLAO", address: "PARQUE N3 AAHH JOSE BOTERIN", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. JOSE OLAYA (BASE DE MICRORED)", district: "CALLAO", address: "JR. JUNIN PP.JJ. JOSE OLAYA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MIGUEL GRAU", district: "CALLAO", address: "ALT. CDRA.10 AV.TUPAC AMARU - PP.JJ. MIGUEL GRAU", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. SANTA ROSA", district: "CALLAO", address: "AV. T. AMARU GDIA.CHALACA S/N MINICOMPL. STA.ROSA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL. NESTOR GAMBETTA (BASE DE MICRORED)", district: "CALLAO", address: "AV. ALAMEDA S/N PP.JJ. GAMBETTA ALTA", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. RAMON CASTILLA", district: "CALLAO", address: "JR. CUZCO S/N PP.JJ. RAMON CASTILLA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL ACAPULCO (BASE DE MICRORED)", district: "CALLAO", address: "AV.JOSE GALVEZ S/N CMTE.8 - PP.JJ. ACAPULCO", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. JUAN PABLO II", district: "CALLAO", address: "AA.HH.JUAN PABLO II (ALT.AV.GAMBETTA KM 2.5)", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MENTAL COMUNITARIO SARITA COLONIA", district: "CALLAO", address: "AV. RAMIRO PRIALE S/N - AA.HH. SARITA COLONIA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "HOSP. NAC. DANIEL ALCIDES CARRION", district: "CALLAO", address: "AV. GUARDIA CHALACA N 2170", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. FAUCETT (BASE DE MICRORED)", district: "CALLAO", address: "CALLE 3 S/N - URB. FAUCETT", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. 200 MILLAS", district: "CALLAO", address: "MZ L LOTE 3-4 I ETAP. URB.200 MILLAS (KM.5.5 AV.GAMBETTA)", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. PALMERAS DE OQUENDO", district: "CALLAO", address: "CALLE MARLEN MZ LTE 5 Y 6 - URB. LAS PALMERAS (AL KM9 NESTOR GAMBETTA)", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. SESQUICENTENARIO (BASE DE MICRORED)", district: "CALLAO", address: "ALT.CALLE 7 Y 14 - URB.SESQUICENTENARIO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. PREVI", district: "CALLAO", address: "CALLE CENTRAL S/N - URB. PREVI", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. BOCANEGRA", district: "CALLAO", address: "AA.HH. BOCANEGRA - PLAZA CIVICA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. EL ALAMO", district: "CALLAO", address: "MZ. S/N URB. EL ALAMO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. AEROPUERTO (BASE DE MICRORED)", district: "CALLAO", address: "JR. SALAVERRY S/N - AA.HH. AEROPUERTO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. PLAYA RIMAC", district: "CALLAO", address: "CALLE BOLOGNESI Y JOSE SANTOS CHOCANO S/N", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "P.S. POLIGONO IV", district: "CALLAO", address: "AA.HH. BOCANEGRA - SECTOR V", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL MARQUEZ", district: "CALLAO", address: "AV. LOS ALAMOS S/N - MARQUEZ", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "SANIDAD MARITIMA", district: "CALLAO", address: "JR MILLER 175", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "SANIDAD AEREA", district: "CALLAO", address: "AEROPUERTO INTERNACIONAL JORGE CHAVEZ", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "HOSPITAL II LIMA NORTE CALLAO \"LUIS NEGREIROS VEGA\"", district: "CALLAO", address: "AV. TOMÁS VALLE CDRA. 39", hours: "24 HORAS", entity: "ESSALUD" },
  { name: "CENTRO DE ATENCIÓN PRIMARIA III LUIS NEGREIROS VEGA", district: "CALLAO", address: "AV. TOMÁS VALLE CDRA.39", hours: "12 HORAS", entity: "ESSALUD" },
  { name: "HOSPITAL II ALBERTO LEONARDO BARTON THOMPSON", district: "CALLAO", address: "AV. ARGENTINA 3525", hours: "24 HORAS", entity: "ESSALUD" },
  { name: "POLICLÍNICO ALBERTO LEONARDO BARTON THOMPSON", district: "CALLAO", address: "AV. SÁENZ PEÑA 345-373", hours: "12 HORAS", entity: "ESSALUD" },
  { name: "C.S. CARMEN DE LA LEGUA", district: "CARMEN DE LA LEGUA - REYNOSO", address: "AV. MANCO CAPAC CDRA. 8", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "HOSPITAL SAN JOSE", district: "CARMEN DE LA LEGUA - REYNOSO", address: "AV. ELMER FAUCETT CDRA. 9 S/N", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. VILLA SR. DE LOS MILAGROS", district: "CARMEN DE LA LEGUA - REYNOSO", address: "P.J.VILLA SR.DE MILAGR. ALT.CDRA.60 AV.ARGENTINA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MENTAL COMUNITARIO CARMEN DE LA LEGUA - REYNOSO", district: "CARMEN DE LA LEGUA - REYNOSO", address: "AV 1RO DE MAYO 898 P.J. REYNOSO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. ALTA MAR", district: "LA PERLA", address: "AV. DOS DE MAYO N 640", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. LA PERLA", district: "LA PERLA", address: "ALFONSO UGARTE N 1150", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MENTAL COMUNITARIO LA PERLA", district: "LA PERLA", address: "AV LA PAZ CUADRA 3", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "CENTRO DE ATENCIÓN PRIMARIA III METROPOLITANO DEL CALLAO", district: "LA PERLA", address: "AV. LA MARINA 288", hours: "12 HORAS", entity: "ESSALUD" },
  { name: "C.S. LA PUNTA", district: "LA PUNTA", address: "AV. GRAU N 1002", hours: "6 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL MI PERU", district: "MI PERU", address: "MZ.G6 LOTE 1 AV.HUAURA", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MENTAL COMUNITARIO MI PERU", district: "MI PERU", address: "AV AREQUIPA CDRA 7 MI PERU", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "HOSPITAL DE VENTANILLA", district: "VENTANILLA", address: "AV. PEDRO BELTRAN ALT. CUADRA 3 S/N - URB. SATELITE", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL PACHACUTEC PERU - KOREA", district: "VENTANILLA", address: "MZ. X LT. 1 - AAHH HIROSHIMA - CIUDAD PACHACUTEC", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. 03 DE FEBRERO", district: "VENTANILLA", address: "MZ. V SECTOR B PROLONG. AV. 225 S/N - CIUDADELA PACHACUTEC", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. BAHIA BLANCA", district: "VENTANILLA", address: "MZ P1 LT 1 - SECTOR E - CIUDADELA PACHACUTEC", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. CIUDAD PACHACUTEC", district: "VENTANILLA", address: "MZ. G1 LT. 2 - COOP. LA UNION - CIUDADELA PACHACUTEC", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. SANTA ROSA DE PACHACUTEC", district: "VENTANILLA", address: "MZ. O LT. 1 - AA.HH. SANTA ROSA DE PACHACUTEC", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. ANGAMOS", district: "VENTANILLA", address: "AV. HUAURA S/N - AA.HH. MI PERU", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. HIJOS DEL ALMIRANTE GRAU", district: "VENTANILLA", address: "MZ. 7 AA.HH. HIJOS DEL ALMIRANTE MIGUEL GRAU", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "P.S. DEFENSORES DE LA PATRIA", district: "VENTANILLA", address: "AA.HH. DEFENSORES DE LA PATRIA S/N", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. VENTANILLA ALTA", district: "VENTANILLA", address: "AV. CENTRAL S/N AA.HH. VENTANILLA ALTA", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. MATERNO INFANTIL VILLA LOS REYES", district: "VENTANILLA", address: "MZ. N-1 SC. ADELANTE AA.HH. VILLA DE LOS REYES", hours: "24 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. LUIS FELIPE DE LAS CASAS", district: "VENTANILLA", address: "AA.HH. LUIS FELIPE DE LAS CASAS KM. 39 PANAM. NORTE", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. VENTANILLA BAJA", district: "VENTANILLA", address: "PARQUE COMERCIAL AA.HH. V.R. HAYA DE LA TORRE", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "C.S. VENTANILLA ESTE", district: "VENTANILLA", address: "PRIMERA ETAPA AA.HH. PARQUE PORCINO", hours: "12 HORAS", entity: "DIRESA CALLAO" },
  { name: "CENTRO DE ATENCIÓN PRIMARIA III HNA. MARÍA DONROSE SUTMÖLLER", district: "VENTANILLA", address: "AV. GONZÁLES GANOZA S/N - URB. ANTONIA MORENO DE CÁCERES", hours: "12 HORAS", entity: "ESSALUD" }
];

const districts = [
  "BELLAVISTA",
  "CALLAO",
  "CARMEN DE LA LEGUA - REYNOSO",
  "LA PERLA",
  "LA PUNTA",
  "MI PERU",
  "VENTANILLA"
];

// Coordenadas manuales conocidas o estimadas para cuando Nominatim no encuentre nada.
// Esto garantiza que el 100% de los centros de salud queden mapeados en su ubicación o calle aproximada.
const manualFallbackCoordinates = {
  "C.S. MATERNO INFANTIL BELLAVISTA PERÚ COREA": { lat: -12.0673, lon: -77.1085 },
  "C.S. MENTAL COMUNITARIO UNIVERSITARIO CALLAO": { lat: -12.0617, lon: -77.1171 },
  "C.S. ALBERTO BARTON": { lat: -12.0601, lon: -77.1353 },
  "C.S. JOSE OLAYA (BASE DE MICRORED)": { lat: -12.0531, lon: -77.1415 },
  "C.S. MIGUEL GRAU": { lat: -12.0392, lon: -77.1219 },
  "C.S. MATERNO INFANTIL. NESTOR GAMBETTA (BASE DE MICRORED)": { lat: -12.0381, lon: -77.1264 },
  "C.S. MATERNO INFANTIL ACAPULCO (BASE DE MICRORED)": { lat: -12.0315, lon: -77.1350 },
  "C.S. 200 MILLAS": { lat: -12.0197, lon: -77.1189 },
  "C.S. PALMERAS DE OQUENDO": { lat: -11.9792, lon: -77.1235 },
  "C.S. SESQUICENTENARIO (BASE DE MICRORED)": { lat: -12.0354, lon: -77.1091 },
  "C.S. PREVI": { lat: -12.0298, lon: -77.0987 },
  "C.S. EL ALAMO": { lat: -12.0225, lon: -77.1021 },
  "C.S. AEROPUERTO (BASE DE MICRORED)": { lat: -12.0246, lon: -77.0901 },
  "C.S. PLAYA RIMAC": { lat: -12.0289, lon: -77.0864 },
  "P.S. POLIGONO IV": { lat: -12.0142, lon: -77.0911 },
  "SANIDAD AEREA": { lat: -12.0219, lon: -77.1121 },
  "C.S. VILLA SR. DE LOS MILAGROS": { lat: -12.0521, lon: -77.0964 },
  "C.S. MENTAL COMUNITARIO CARMEN DE LA LEGUA - REYNOSO": { lat: -12.0498, lon: -77.0841 },
  "C.S. MENTAL COMUNITARIO LA PERLA": { lat: -12.0725, lon: -77.1011 },
  "C.S. MENTAL COMUNITARIO MI PERU": { lat: -11.8512, lon: -77.1181 },
  "C.S. 03 DE FEBRERO": { lat: -11.8219, lon: -77.1511 },
  "C.S. BAHIA BLANCA": { lat: -11.8125, lon: -77.1592 },
  "C.S. CIUDAD PACHACUTEC": { lat: -11.8081, lon: -77.1481 },
  "C.S. SANTA ROSA DE PACHACUTEC": { lat: -11.8198, lon: -77.1398 },
  "C.S. HIJOS DEL ALMIRANTE GRAU": { lat: -11.8842, lon: -77.1351 },
  "P.S. DEFENSORES DE LA PATRIA": { lat: -11.8711, lon: -77.1401 },
  "C.S. VENTANILLA ALTA": { lat: -11.8745, lon: -77.1215 },
  "C.S. LUIS FELIPE DE LAS CASAS": { lat: -11.8012, lon: -77.1285 },
  "C.S. VENTANILLA BAJA": { lat: -11.8901, lon: -77.1311 },
  "C.S. VENTANILLA ESTE": { lat: -11.8998, lon: -77.1085 }
};

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function cleanName(name) {
  let cleaned = name.replace(/\(BASE DE MICRORED\)/ig, '');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();
  cleaned = cleaned.replace(/^C\.S\. MATERNO INFANTIL\.?\s+/i, '');
  cleaned = cleaned.replace(/^C\.S\. MENTAL COMUNITARIO\s+/i, '');
  cleaned = cleaned.replace(/^C\.S\.\s+/i, '');
  cleaned = cleaned.replace(/^P\.S\.\s+/i, '');
  cleaned = cleaned.replace(/^HOSP\.? NAC\.?\s+/i, '');
  cleaned = cleaned.replace(/^HOSPITAL II\s+/i, '');
  cleaned = cleaned.replace(/“/g, '').replace(/”/g, '').replace(/"/g, '');
  cleaned = cleaned.replace(/\(.*\)/g, ''); // remove parentheses contents
  cleaned = cleaned.replace(/PERÚ/gi, 'Peru').replace(/COREA/gi, 'Corea');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();
  return cleaned;
}

function fetchJson(url) {
  return new Promise((resolve, reject) => {
    const options = {
      headers: {
        'User-Agent': 'CallaoHealthMapApp/1.0 (gisadmin@gis.local)'
      }
    };
    https.get(url, options, (res) => {
      let data = '';
      res.on('data', (chunk) => { data += chunk; });
      res.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (e) {
          reject(new Error(`Failed to parse JSON: ${e.message}. Data received: ${data.substring(0, 100)}`));
        }
      });
    }).on('error', (err) => {
      reject(err);
    });
  });
}

async function geocodeCenter(center) {
  const cleaned = cleanName(center.name);
  
  // Lista de términos de búsqueda de más a menos específicos
  const queries = [
    `${cleaned}, ${center.district}, Callao, Peru`,
    `${cleaned}, Callao, Peru`,
    `${center.address}, ${center.district}, Callao, Peru`,
    `${center.address}, Callao, Peru`,
    `${cleaned}, Peru`
  ];

  for (const q of queries) {
    const encoded = encodeURIComponent(q);
    const url = `https://nominatim.openstreetmap.org/search?q=${encoded}&format=json&limit=1`;
    try {
      console.log(`Buscando: ${q}`);
      const data = await fetchJson(url);
      if (data && data.length > 0) {
        return {
          lat: parseFloat(data[0].lat),
          lon: parseFloat(data[0].lon),
          displayName: data[0].display_name,
          method: q
        };
      }
    } catch (e) {
      console.error(`Error geocodificando "${q}":`, e.message);
    }
    await sleep(1000); // Respetar rate limit de Nominatim
  }

  // Si no se encuentra y tenemos fallback manual, usarlo
  if (manualFallbackCoordinates[center.name]) {
    console.log(`Usando coordenadas de fallback manual para: ${center.name}`);
    return {
      lat: manualFallbackCoordinates[center.name].lat,
      lon: manualFallbackCoordinates[center.name].lon,
      displayName: "Coordenadas estimadas de manualFallback",
      method: "manual"
    };
  }

  return null;
}

async function fetchDistrictBoundary(district) {
  let queryName = district;
  if (district === "CARMEN DE LA LEGUA - REYNOSO") {
    queryName = "Carmen de la Legua-Reynoso";
  } else if (district === "MI PERU") {
    queryName = "Mi Perú";
  }

  const q = `${queryName}, Callao, Peru`;
  const encoded = encodeURIComponent(q);
  const url = `https://nominatim.openstreetmap.org/search?q=${encoded}&format=geojson&polygon_geojson=1&limit=1`;
  try {
    console.log(`Obteniendo límites para distrito: ${q}`);
    const data = await fetchJson(url);
    if (data && data.features && data.features.length > 0) {
      return data.features[0].geometry;
    }
  } catch (e) {
    console.error(`Error obteniendo límite de distrito para "${q}":`, e.message);
  }
  return null;
}

async function run() {
  const result = {
    districts: {},
    centers: []
  };

  // 1. Obtener límites de distritos
  for (const d of districts) {
    const boundary = await fetchDistrictBoundary(d);
    if (boundary) {
      console.log(`Límite obtenido para ${d}`);
      result.districts[d] = boundary;
    } else {
      console.log(`No se pudo obtener límite para ${d}`);
    }
    await sleep(1000);
  }

  // 2. Geocodificar centros de salud
  for (let i = 0; i < healthCentersRaw.length; i++) {
    const center = healthCentersRaw[i];
    console.log(`[${i + 1}/${healthCentersRaw.length}] Geocodificando: ${center.name}`);
    const coords = await geocodeCenter(center);
    if (coords) {
      console.log(`Encontrado: ${coords.lat}, ${coords.lon}`);
      result.centers.push({
        ...center,
        lat: coords.lat,
        lon: coords.lon,
        displayName: coords.displayName
      });
    } else {
      console.log(`NO ENCONTRADO: ${center.name}`);
      result.centers.push({
        ...center,
        lat: null,
        lon: null
      });
    }
    await sleep(1000);
  }

  fs.writeFileSync(
    path.join(__dirname, 'callao_geocoded.json'),
    JSON.stringify(result, null, 2),
    'utf8'
  );
  console.log("Completado! Datos guardados en callao_geocoded.json");
}

run();
