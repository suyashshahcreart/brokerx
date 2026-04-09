<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

class UsaStateCitySeeder extends Seeder
{
    /**
     * Seed US states and cities. Requires countries row for United States (CountrySeeder).
     */
    public function run(): void
    {
        $country = Country::query()->where('name', 'United States')->first();

        if (! $country) {
            $this->command?->warn('United States not found in countries. Run CountrySeeder first.');

            return;
        }

        $countryId = $country->id;

        foreach ($this->usaStatesAndCities() as $row) {
            $state = State::query()->firstOrCreate(
                [
                    'country_id' => $countryId,
                    'code' => $row['code'],
                ],
                [
                    'name' => $row['name'],
                ]
            );

            if ($state->name !== $row['name']) {
                $state->update(['name' => $row['name']]);
            }

            foreach ($row['cities'] as $cityName) {
                City::query()->firstOrCreate(
                    [
                        'state_id' => $state->id,
                        'name' => $cityName,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array{name: string, code: string, cities: list<string>}>
     */
    private function usaStatesAndCities(): array
    {
        return [
            ['name' => 'Alabama', 'code' => 'AL', 'cities' => ['Birmingham', 'Montgomery', 'Mobile', 'Huntsville', 'Tuscaloosa']],
            ['name' => 'Alaska', 'code' => 'AK', 'cities' => ['Anchorage', 'Fairbanks', 'Juneau', 'Sitka']],
            ['name' => 'Arizona', 'code' => 'AZ', 'cities' => ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Glendale']],
            ['name' => 'Arkansas', 'code' => 'AR', 'cities' => ['Little Rock', 'Fort Smith', 'Fayetteville', 'Springdale', 'Jonesboro']],
            ['name' => 'California', 'code' => 'CA', 'cities' => ['Los Angeles', 'San Diego', 'San Jose', 'San Francisco', 'Sacramento', 'Fresno', 'Oakland', 'Long Beach']],
            ['name' => 'Colorado', 'code' => 'CO', 'cities' => ['Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood', 'Boulder']],
            ['name' => 'Connecticut', 'code' => 'CT', 'cities' => ['Bridgeport', 'New Haven', 'Hartford', 'Stamford', 'Waterbury']],
            ['name' => 'Delaware', 'code' => 'DE', 'cities' => ['Wilmington', 'Dover', 'Newark', 'Middletown']],
            ['name' => 'Florida', 'code' => 'FL', 'cities' => ['Jacksonville', 'Miami', 'Tampa', 'Orlando', 'St. Petersburg', 'Hialeah', 'Tallahassee', 'Fort Lauderdale']],
            ['name' => 'Georgia', 'code' => 'GA', 'cities' => ['Atlanta', 'Augusta', 'Columbus', 'Savannah', 'Athens', 'Macon']],
            ['name' => 'Hawaii', 'code' => 'HI', 'cities' => ['Honolulu', 'Hilo', 'Kailua', 'Kapolei']],
            ['name' => 'Idaho', 'code' => 'ID', 'cities' => ['Boise', 'Meridian', 'Nampa', 'Idaho Falls', 'Pocatello']],
            ['name' => 'Illinois', 'code' => 'IL', 'cities' => ['Chicago', 'Aurora', 'Rockford', 'Joliet', 'Naperville', 'Springfield', 'Peoria']],
            ['name' => 'Indiana', 'code' => 'IN', 'cities' => ['Indianapolis', 'Fort Wayne', 'Evansville', 'South Bend', 'Carmel', 'Bloomington']],
            ['name' => 'Iowa', 'code' => 'IA', 'cities' => ['Des Moines', 'Cedar Rapids', 'Davenport', 'Sioux City', 'Iowa City', 'Waterloo']],
            ['name' => 'Kansas', 'code' => 'KS', 'cities' => ['Wichita', 'Overland Park', 'Kansas City', 'Olathe', 'Topeka', 'Lawrence']],
            ['name' => 'Kentucky', 'code' => 'KY', 'cities' => ['Louisville', 'Lexington', 'Bowling Green', 'Owensboro', 'Covington']],
            ['name' => 'Louisiana', 'code' => 'LA', 'cities' => ['New Orleans', 'Baton Rouge', 'Shreveport', 'Lafayette', 'Lake Charles']],
            ['name' => 'Maine', 'code' => 'ME', 'cities' => ['Portland', 'Lewiston', 'Bangor', 'South Portland', 'Augusta']],
            ['name' => 'Maryland', 'code' => 'MD', 'cities' => ['Baltimore', 'Frederick', 'Rockville', 'Gaithersburg', 'Annapolis']],
            ['name' => 'Massachusetts', 'code' => 'MA', 'cities' => ['Boston', 'Worcester', 'Springfield', 'Cambridge', 'Lowell', 'New Bedford']],
            ['name' => 'Michigan', 'code' => 'MI', 'cities' => ['Detroit', 'Grand Rapids', 'Warren', 'Sterling Heights', 'Ann Arbor', 'Lansing']],
            ['name' => 'Minnesota', 'code' => 'MN', 'cities' => ['Minneapolis', 'Saint Paul', 'Rochester', 'Duluth', 'Bloomington']],
            ['name' => 'Mississippi', 'code' => 'MS', 'cities' => ['Jackson', 'Gulfport', 'Southaven', 'Hattiesburg', 'Biloxi']],
            ['name' => 'Missouri', 'code' => 'MO', 'cities' => ['Kansas City', 'Saint Louis', 'Springfield', 'Columbia', 'Independence', 'Jefferson City']],
            ['name' => 'Montana', 'code' => 'MT', 'cities' => ['Billings', 'Missoula', 'Great Falls', 'Bozeman', 'Helena']],
            ['name' => 'Nebraska', 'code' => 'NE', 'cities' => ['Omaha', 'Lincoln', 'Bellevue', 'Grand Island', 'Kearney']],
            ['name' => 'Nevada', 'code' => 'NV', 'cities' => ['Las Vegas', 'Henderson', 'Reno', 'North Las Vegas', 'Carson City']],
            ['name' => 'New Hampshire', 'code' => 'NH', 'cities' => ['Manchester', 'Nashua', 'Concord', 'Dover', 'Rochester']],
            ['name' => 'New Jersey', 'code' => 'NJ', 'cities' => ['Newark', 'Jersey City', 'Paterson', 'Elizabeth', 'Trenton', 'Atlantic City']],
            ['name' => 'New Mexico', 'code' => 'NM', 'cities' => ['Albuquerque', 'Las Cruces', 'Rio Rancho', 'Santa Fe', 'Roswell']],
            ['name' => 'New York', 'code' => 'NY', 'cities' => ['New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany']],
            ['name' => 'North Carolina', 'code' => 'NC', 'cities' => ['Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Asheville']],
            ['name' => 'North Dakota', 'code' => 'ND', 'cities' => ['Fargo', 'Bismarck', 'Grand Forks', 'Minot']],
            ['name' => 'Ohio', 'code' => 'OH', 'cities' => ['Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton']],
            ['name' => 'Oklahoma', 'code' => 'OK', 'cities' => ['Oklahoma City', 'Tulsa', 'Norman', 'Broken Arrow', 'Edmond']],
            ['name' => 'Oregon', 'code' => 'OR', 'cities' => ['Portland', 'Salem', 'Eugene', 'Gresham', 'Bend']],
            ['name' => 'Pennsylvania', 'code' => 'PA', 'cities' => ['Philadelphia', 'Pittsburgh', 'Allentown', 'Erie', 'Harrisburg', 'Reading']],
            ['name' => 'Rhode Island', 'code' => 'RI', 'cities' => ['Providence', 'Warwick', 'Cranston', 'Pawtucket', 'Newport']],
            ['name' => 'South Carolina', 'code' => 'SC', 'cities' => ['Charleston', 'Columbia', 'North Charleston', 'Mount Pleasant', 'Greenville']],
            ['name' => 'South Dakota', 'code' => 'SD', 'cities' => ['Sioux Falls', 'Rapid City', 'Aberdeen', 'Pierre']],
            ['name' => 'Tennessee', 'code' => 'TN', 'cities' => ['Nashville', 'Memphis', 'Knoxville', 'Chattanooga', 'Clarksville']],
            ['name' => 'Texas', 'code' => 'TX', 'cities' => ['Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso', 'Arlington', 'Corpus Christi']],
            ['name' => 'Utah', 'code' => 'UT', 'cities' => ['Salt Lake City', 'West Valley City', 'Provo', 'West Jordan', 'Ogden']],
            ['name' => 'Vermont', 'code' => 'VT', 'cities' => ['Burlington', 'Essex', 'South Burlington', 'Montpelier', 'Rutland']],
            ['name' => 'Virginia', 'code' => 'VA', 'cities' => ['Virginia Beach', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria', 'Arlington']],
            ['name' => 'Washington', 'code' => 'WA', 'cities' => ['Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Olympia']],
            ['name' => 'West Virginia', 'code' => 'WV', 'cities' => ['Charleston', 'Huntington', 'Morgantown', 'Parkersburg', 'Wheeling']],
            ['name' => 'Wisconsin', 'code' => 'WI', 'cities' => ['Milwaukee', 'Madison', 'Green Bay', 'Kenosha', 'Racine']],
            ['name' => 'Wyoming', 'code' => 'WY', 'cities' => ['Cheyenne', 'Casper', 'Laramie', 'Gillette', 'Rock Springs']],
            ['name' => 'District of Columbia', 'code' => 'DC', 'cities' => ['Washington']],
        ];
    }
}
