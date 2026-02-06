<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // A
            ['name' => 'Afghanistan', 'country_code' => 'AF', 'dial_code' => '+93', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Albania', 'country_code' => 'AL', 'dial_code' => '+355', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Algeria', 'country_code' => 'DZ', 'dial_code' => '+213', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Andorra', 'country_code' => 'AD', 'dial_code' => '+376', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Angola', 'country_code' => 'AO', 'dial_code' => '+244', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Antigua and Barbuda', 'country_code' => 'AG', 'dial_code' => '+1-268', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Argentina', 'country_code' => 'AR', 'dial_code' => '+54', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Armenia', 'country_code' => 'AM', 'dial_code' => '+374', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Australia', 'country_code' => 'AU', 'dial_code' => '+61', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Austria', 'country_code' => 'AT', 'dial_code' => '+43', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Azerbaijan', 'country_code' => 'AZ', 'dial_code' => '+994', 'flag_icon' => null, 'is_active' => false],

            // B
            ['name' => 'Bahamas', 'country_code' => 'BS', 'dial_code' => '+1-242', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bahrain', 'country_code' => 'BH', 'dial_code' => '+973', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bangladesh', 'country_code' => 'BD', 'dial_code' => '+880', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Barbados', 'country_code' => 'BB', 'dial_code' => '+1-246', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Belarus', 'country_code' => 'BY', 'dial_code' => '+375', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Belgium', 'country_code' => 'BE', 'dial_code' => '+32', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Belize', 'country_code' => 'BZ', 'dial_code' => '+501', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Benin', 'country_code' => 'BJ', 'dial_code' => '+229', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bhutan', 'country_code' => 'BT', 'dial_code' => '+975', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bolivia', 'country_code' => 'BO', 'dial_code' => '+591', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bosnia and Herzegovina', 'country_code' => 'BA', 'dial_code' => '+387', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Botswana', 'country_code' => 'BW', 'dial_code' => '+267', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Brazil', 'country_code' => 'BR', 'dial_code' => '+55', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Brunei', 'country_code' => 'BN', 'dial_code' => '+673', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Bulgaria', 'country_code' => 'BG', 'dial_code' => '+359', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Burkina Faso', 'country_code' => 'BF', 'dial_code' => '+226', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Burundi', 'country_code' => 'BI', 'dial_code' => '+257', 'flag_icon' => null, 'is_active' => false],

            // C
            ['name' => 'Cambodia', 'country_code' => 'KH', 'dial_code' => '+855', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Cameroon', 'country_code' => 'CM', 'dial_code' => '+237', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Canada', 'country_code' => 'CA', 'dial_code' => '+1', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Cape Verde', 'country_code' => 'CV', 'dial_code' => '+238', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Central African Republic', 'country_code' => 'CF', 'dial_code' => '+236', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Chad', 'country_code' => 'TD', 'dial_code' => '+235', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Chile', 'country_code' => 'CL', 'dial_code' => '+56', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'China', 'country_code' => 'CN', 'dial_code' => '+86', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Colombia', 'country_code' => 'CO', 'dial_code' => '+57', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Comoros', 'country_code' => 'KM', 'dial_code' => '+269', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Congo', 'country_code' => 'CG', 'dial_code' => '+242', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Congo (DRC)', 'country_code' => 'CD', 'dial_code' => '+243', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Costa Rica', 'country_code' => 'CR', 'dial_code' => '+506', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Croatia', 'country_code' => 'HR', 'dial_code' => '+385', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Cuba', 'country_code' => 'CU', 'dial_code' => '+53', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Cyprus', 'country_code' => 'CY', 'dial_code' => '+357', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Czech Republic', 'country_code' => 'CZ', 'dial_code' => '+420', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Côte d\'Ivoire', 'country_code' => 'CI', 'dial_code' => '+225', 'flag_icon' => null, 'is_active' => false],

            // D
            ['name' => 'Denmark', 'country_code' => 'DK', 'dial_code' => '+45', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Djibouti', 'country_code' => 'DJ', 'dial_code' => '+253', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Dominica', 'country_code' => 'DM', 'dial_code' => '+1-767', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Dominican Republic', 'country_code' => 'DO', 'dial_code' => '+1-809', 'flag_icon' => null, 'is_active' => false],

            // E
            ['name' => 'Ecuador', 'country_code' => 'EC', 'dial_code' => '+593', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Egypt', 'country_code' => 'EG', 'dial_code' => '+20', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'El Salvador', 'country_code' => 'SV', 'dial_code' => '+503', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Equatorial Guinea', 'country_code' => 'GQ', 'dial_code' => '+240', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Eritrea', 'country_code' => 'ER', 'dial_code' => '+291', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Estonia', 'country_code' => 'EE', 'dial_code' => '+372', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Eswatini', 'country_code' => 'SZ', 'dial_code' => '+268', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Ethiopia', 'country_code' => 'ET', 'dial_code' => '+251', 'flag_icon' => null, 'is_active' => false],

            // F
            ['name' => 'Fiji', 'country_code' => 'FJ', 'dial_code' => '+679', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Finland', 'country_code' => 'FI', 'dial_code' => '+358', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'France', 'country_code' => 'FR', 'dial_code' => '+33', 'flag_icon' => null, 'is_active' => true],

            // G
            ['name' => 'Gabon', 'country_code' => 'GA', 'dial_code' => '+241', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Gambia', 'country_code' => 'GM', 'dial_code' => '+220', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Georgia', 'country_code' => 'GE', 'dial_code' => '+995', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Germany', 'country_code' => 'DE', 'dial_code' => '+49', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'Ghana', 'country_code' => 'GH', 'dial_code' => '+233', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Greece', 'country_code' => 'GR', 'dial_code' => '+30', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Grenada', 'country_code' => 'GD', 'dial_code' => '+1-473', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Guatemala', 'country_code' => 'GT', 'dial_code' => '+502', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Guinea', 'country_code' => 'GN', 'dial_code' => '+224', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Guinea-Bissau', 'country_code' => 'GW', 'dial_code' => '+245', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Guyana', 'country_code' => 'GY', 'dial_code' => '+592', 'flag_icon' => null, 'is_active' => false],

            // H
            ['name' => 'Haiti', 'country_code' => 'HT', 'dial_code' => '+509', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Honduras', 'country_code' => 'HN', 'dial_code' => '+504', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Hungary', 'country_code' => 'HU', 'dial_code' => '+36', 'flag_icon' => null, 'is_active' => false],

            // I
            ['name' => 'Iceland', 'country_code' => 'IS', 'dial_code' => '+354', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'India', 'country_code' => 'IN', 'dial_code' => '+91', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'Indonesia', 'country_code' => 'ID', 'dial_code' => '+62', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Iran', 'country_code' => 'IR', 'dial_code' => '+98', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Iraq', 'country_code' => 'IQ', 'dial_code' => '+964', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Ireland', 'country_code' => 'IE', 'dial_code' => '+353', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Israel', 'country_code' => 'IL', 'dial_code' => '+972', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Italy', 'country_code' => 'IT', 'dial_code' => '+39', 'flag_icon' => null, 'is_active' => false],

            // J
            ['name' => 'Jamaica', 'country_code' => 'JM', 'dial_code' => '+1-876', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Japan', 'country_code' => 'JP', 'dial_code' => '+81', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Jordan', 'country_code' => 'JO', 'dial_code' => '+962', 'flag_icon' => null, 'is_active' => false],

            // K
            ['name' => 'Kazakhstan', 'country_code' => 'KZ', 'dial_code' => '+7', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Kenya', 'country_code' => 'KE', 'dial_code' => '+254', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Kiribati', 'country_code' => 'KI', 'dial_code' => '+686', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Kosovo', 'country_code' => 'XK', 'dial_code' => '+383', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Kuwait', 'country_code' => 'KW', 'dial_code' => '+965', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Kyrgyzstan', 'country_code' => 'KG', 'dial_code' => '+996', 'flag_icon' => null, 'is_active' => false],

            // L
            ['name' => 'Laos', 'country_code' => 'LA', 'dial_code' => '+856', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Latvia', 'country_code' => 'LV', 'dial_code' => '+371', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Lebanon', 'country_code' => 'LB', 'dial_code' => '+961', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Lesotho', 'country_code' => 'LS', 'dial_code' => '+266', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Liberia', 'country_code' => 'LR', 'dial_code' => '+231', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Libya', 'country_code' => 'LY', 'dial_code' => '+218', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Liechtenstein', 'country_code' => 'LI', 'dial_code' => '+423', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Lithuania', 'country_code' => 'LT', 'dial_code' => '+370', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Luxembourg', 'country_code' => 'LU', 'dial_code' => '+352', 'flag_icon' => null, 'is_active' => false],

            // M
            ['name' => 'Madagascar', 'country_code' => 'MG', 'dial_code' => '+261', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Malawi', 'country_code' => 'MW', 'dial_code' => '+265', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Malaysia', 'country_code' => 'MY', 'dial_code' => '+60', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Maldives', 'country_code' => 'MV', 'dial_code' => '+960', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mali', 'country_code' => 'ML', 'dial_code' => '+223', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Malta', 'country_code' => 'MT', 'dial_code' => '+356', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Marshall Islands', 'country_code' => 'MH', 'dial_code' => '+692', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mauritania', 'country_code' => 'MR', 'dial_code' => '+222', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mauritius', 'country_code' => 'MU', 'dial_code' => '+230', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mexico', 'country_code' => 'MX', 'dial_code' => '+52', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Micronesia', 'country_code' => 'FM', 'dial_code' => '+691', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Moldova', 'country_code' => 'MD', 'dial_code' => '+373', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Monaco', 'country_code' => 'MC', 'dial_code' => '+377', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mongolia', 'country_code' => 'MN', 'dial_code' => '+976', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Montenegro', 'country_code' => 'ME', 'dial_code' => '+382', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Morocco', 'country_code' => 'MA', 'dial_code' => '+212', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Mozambique', 'country_code' => 'MZ', 'dial_code' => '+258', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Myanmar', 'country_code' => 'MM', 'dial_code' => '+95', 'flag_icon' => null, 'is_active' => false],

            // N
            ['name' => 'Namibia', 'country_code' => 'NA', 'dial_code' => '+264', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Nauru', 'country_code' => 'NR', 'dial_code' => '+674', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Nepal', 'country_code' => 'NP', 'dial_code' => '+977', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Netherlands', 'country_code' => 'NL', 'dial_code' => '+31', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'New Zealand', 'country_code' => 'NZ', 'dial_code' => '+64', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Nicaragua', 'country_code' => 'NI', 'dial_code' => '+505', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Niger', 'country_code' => 'NE', 'dial_code' => '+227', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Nigeria', 'country_code' => 'NG', 'dial_code' => '+234', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'North Korea', 'country_code' => 'KP', 'dial_code' => '+850', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'North Macedonia', 'country_code' => 'MK', 'dial_code' => '+389', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Norway', 'country_code' => 'NO', 'dial_code' => '+47', 'flag_icon' => null, 'is_active' => false],

            // O
            ['name' => 'Oman', 'country_code' => 'OM', 'dial_code' => '+968', 'flag_icon' => null, 'is_active' => false],

            // P
            ['name' => 'Pakistan', 'country_code' => 'PK', 'dial_code' => '+92', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Palau', 'country_code' => 'PW', 'dial_code' => '+680', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Palestine', 'country_code' => 'PS', 'dial_code' => '+970', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Panama', 'country_code' => 'PA', 'dial_code' => '+507', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Papua New Guinea', 'country_code' => 'PG', 'dial_code' => '+675', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Paraguay', 'country_code' => 'PY', 'dial_code' => '+595', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Peru', 'country_code' => 'PE', 'dial_code' => '+51', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Philippines', 'country_code' => 'PH', 'dial_code' => '+63', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Poland', 'country_code' => 'PL', 'dial_code' => '+48', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Portugal', 'country_code' => 'PT', 'dial_code' => '+351', 'flag_icon' => null, 'is_active' => false],

            // Q
            ['name' => 'Qatar', 'country_code' => 'QA', 'dial_code' => '+974', 'flag_icon' => null, 'is_active' => false],

            // R
            ['name' => 'Romania', 'country_code' => 'RO', 'dial_code' => '+40', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Russia', 'country_code' => 'RU', 'dial_code' => '+7', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Rwanda', 'country_code' => 'RW', 'dial_code' => '+250', 'flag_icon' => null, 'is_active' => false],

            // S
            ['name' => 'Saint Kitts and Nevis', 'country_code' => 'KN', 'dial_code' => '+1-869', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Saint Lucia', 'country_code' => 'LC', 'dial_code' => '+1-758', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Saint Vincent and the Grenadines', 'country_code' => 'VC', 'dial_code' => '+1-784', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Samoa', 'country_code' => 'WS', 'dial_code' => '+685', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'San Marino', 'country_code' => 'SM', 'dial_code' => '+378', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Sao Tome and Principe', 'country_code' => 'ST', 'dial_code' => '+239', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Saudi Arabia', 'country_code' => 'SA', 'dial_code' => '+966', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Senegal', 'country_code' => 'SN', 'dial_code' => '+221', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Serbia', 'country_code' => 'RS', 'dial_code' => '+381', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Seychelles', 'country_code' => 'SC', 'dial_code' => '+248', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Sierra Leone', 'country_code' => 'SL', 'dial_code' => '+232', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Singapore', 'country_code' => 'SG', 'dial_code' => '+65', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Slovakia', 'country_code' => 'SK', 'dial_code' => '+421', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Slovenia', 'country_code' => 'SI', 'dial_code' => '+386', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Solomon Islands', 'country_code' => 'SB', 'dial_code' => '+677', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Somalia', 'country_code' => 'SO', 'dial_code' => '+252', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'South Africa', 'country_code' => 'ZA', 'dial_code' => '+27', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'South Korea', 'country_code' => 'KR', 'dial_code' => '+82', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'South Sudan', 'country_code' => 'SS', 'dial_code' => '+211', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Spain', 'country_code' => 'ES', 'dial_code' => '+34', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Sri Lanka', 'country_code' => 'LK', 'dial_code' => '+94', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Sudan', 'country_code' => 'SD', 'dial_code' => '+249', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Suriname', 'country_code' => 'SR', 'dial_code' => '+597', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Sweden', 'country_code' => 'SE', 'dial_code' => '+46', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Switzerland', 'country_code' => 'CH', 'dial_code' => '+41', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Syria', 'country_code' => 'SY', 'dial_code' => '+963', 'flag_icon' => null, 'is_active' => false],

            // T
            ['name' => 'Taiwan', 'country_code' => 'TW', 'dial_code' => '+886', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Tajikistan', 'country_code' => 'TJ', 'dial_code' => '+992', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Tanzania', 'country_code' => 'TZ', 'dial_code' => '+255', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Thailand', 'country_code' => 'TH', 'dial_code' => '+66', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Timor-Leste', 'country_code' => 'TL', 'dial_code' => '+670', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Togo', 'country_code' => 'TG', 'dial_code' => '+228', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Tonga', 'country_code' => 'TO', 'dial_code' => '+676', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Trinidad and Tobago', 'country_code' => 'TT', 'dial_code' => '+1-868', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Tunisia', 'country_code' => 'TN', 'dial_code' => '+216', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Turkey', 'country_code' => 'TR', 'dial_code' => '+90', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Turkmenistan', 'country_code' => 'TM', 'dial_code' => '+993', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Tuvalu', 'country_code' => 'TV', 'dial_code' => '+688', 'flag_icon' => null, 'is_active' => false],

            // U
            ['name' => 'Uganda', 'country_code' => 'UG', 'dial_code' => '+256', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Ukraine', 'country_code' => 'UA', 'dial_code' => '+380', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'United Arab Emirates', 'country_code' => 'AE', 'dial_code' => '+971', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'United Kingdom', 'country_code' => 'GB', 'dial_code' => '+44', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'United States', 'country_code' => 'US', 'dial_code' => '+1', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'Uruguay', 'country_code' => 'UY', 'dial_code' => '+598', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Uzbekistan', 'country_code' => 'UZ', 'dial_code' => '+998', 'flag_icon' => null, 'is_active' => false],

            // V
            ['name' => 'Vanuatu', 'country_code' => 'VU', 'dial_code' => '+678', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Vatican City', 'country_code' => 'VA', 'dial_code' => '+379', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Venezuela', 'country_code' => 'VE', 'dial_code' => '+58', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Vietnam', 'country_code' => 'VN', 'dial_code' => '+84', 'flag_icon' => null, 'is_active' => false],

            // Y
            ['name' => 'Yemen', 'country_code' => 'YE', 'dial_code' => '+967', 'flag_icon' => null, 'is_active' => false],

            // Z
            ['name' => 'Zambia', 'country_code' => 'ZM', 'dial_code' => '+260', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Zimbabwe', 'country_code' => 'ZW', 'dial_code' => '+263', 'flag_icon' => null, 'is_active' => false],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['country_code' => $country['country_code']],
                $country
            );
        }
    }
}
