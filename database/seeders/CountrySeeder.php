<?php
namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // name, ISO2, dial code, currency
        $rows = [
            ['Algeria','DZ','+213','DZD'],['Angola','AO','+244','AOA'],['Benin','BJ','+229','XOF'],['Botswana','BW','+267','BWP'],['Burkina Faso','BF','+226','XOF'],
            ['Burundi','BI','+257','BIF'],['Cabo Verde','CV','+238','CVE'],['Cameroon','CM','+237','XAF'],['Central African Republic','CF','+236','XAF'],['Chad','TD','+235','XAF'],
            ['Comoros','KM','+269','KMF'],['Congo (Republic)','CG','+242','XAF'],['Congo (DR)','CD','+243','CDF'],["Côte d'Ivoire",'CI','+225','XOF'],['Djibouti','DJ','+253','DJF'],
            ['Egypt','EG','+20','EGP'],['Equatorial Guinea','GQ','+240','XAF'],['Eritrea','ER','+291','ERN'],['Eswatini','SZ','+268','SZL'],['Ethiopia','ET','+251','ETB'],
            ['Gabon','GA','+241','XAF'],['Gambia','GM','+220','GMD'],['Ghana','GH','+233','GHS'],['Guinea','GN','+224','GNF'],['Guinea-Bissau','GW','+245','XOF'],
            ['Kenya','KE','+254','KES'],['Lesotho','LS','+266','LSL'],['Liberia','LR','+231','LRD'],['Libya','LY','+218','LYD'],['Madagascar','MG','+261','MGA'],
            ['Malawi','MW','+265','MWK'],['Mali','ML','+223','XOF'],['Mauritania','MR','+222','MRU'],['Mauritius','MU','+230','MUR'],['Morocco','MA','+212','MAD'],
            ['Mozambique','MZ','+258','MZN'],['Namibia','NA','+264','NAD'],['Niger','NE','+227','XOF'],['Nigeria','NG','+234','NGN'],['Rwanda','RW','+250','RWF'],
            ['São Tomé and Príncipe','ST','+239','STN'],['Senegal','SN','+221','XOF'],['Seychelles','SC','+248','SCR'],['Sierra Leone','SL','+232','SLE'],['Somalia','SO','+252','SOS'],
            ['South Africa','ZA','+27','ZAR'],['South Sudan','SS','+211','SSP'],['Sudan','SD','+249','SDG'],['Tanzania','TZ','+255','TZS'],['Togo','TG','+228','XOF'],
            ['Tunisia','TN','+216','TND'],['Uganda','UG','+256','UGX'],['Zambia','ZM','+260','ZMW'],['Zimbabwe','ZW','+263','USD'],
            ['Other (outside Africa)','XX','+1','USD'],
        ];
        foreach ($rows as [$n, $c, $d, $cur]) { Country::updateOrCreate(['iso2' => $c], ['name' => $n, 'dial_code' => $d, 'currency' => $cur]); }
    }
}
