<?php

namespace App\Http\Controllers\SuperAdmin; // Ensure this namespace is correct

use App\Http\Controllers\Controller;    // Make sure to use the base Controller
use Illuminate\Http\Request;
// You might use a settings model or store settings in config/database
// For simplicity, let's assume for now settings are few and perhaps stored in .env or a simple table/config

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch current settings.
        // For this example, let's imagine some settings.
        // In a real app, these would come from a database, config file, or a dedicated settings service.
        $settings = [
            'restaurant_name' => config('app.name', 'My Restaurant'), // Example: get from config
            'default_currency' => 'USD', // Example hardcoded for now
            'kitchen_printer_ip' => '192.168.1.100', // Example
            'service_charge_percentage' => 10, // Example
            'tax_rate_percentage' => 5, // Example
        ];

        // You would typically load actual settings from your storage mechanism here.
        // e.g., $settings = Setting::all()->pluck('value', 'key');

        return view('superadmin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'sometimes|required|string|max:255',
            'default_currency' => 'sometimes|required|string|max:3',
            'kitchen_printer_ip' => 'nullable|ip',
            'service_charge_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_rate_percentage' => 'nullable|numeric|min:0|max:100',
            // Add validation for other settings you implement
        ]);

        // Logic to save the settings.
        // This is highly dependent on how you choose to store settings.
        // Example: Updating .env (not generally recommended for runtime changes directly for all settings)
        // Example: Updating a 'settings' table in the database
        // Example: Updating a custom config file (config/custom_settings.php)

        // For demonstration, let's just flash a message
        // foreach ($request->except('_token', '_method') as $key => $value) {
        //     // UpdateSetting($key, $value); // Your function/logic to save setting
        //     // For example, if using a simple key-value table:
        //     // Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        // }


        // A simple way to update .env (use with caution, better to use a database or config for most settings)
        // if ($request->has('restaurant_name')) {
        //     $this->updateEnv(['APP_NAME' => $request->restaurant_name]);
        // }

        // A more robust approach is to have a settings table or a dedicated config file
        // that you can read from and write to.

        return redirect()->route('superadmin.settings.index')
                         ->with('success', 'Settings updated successfully.');
    }


    /**
     * Helper function to update .env file (use with extreme caution)
     * This is a simplified example and might not be robust for all .env formats.
     * It's generally better to manage application settings through database or config files
     * that can be cached, rather than directly modifying .env at runtime frequently.
     */
    // protected function updateEnv(array $data)
    // {
    //     $envFilePath = base_path('.env');
    //     if (file_exists($envFilePath)) {
    //         $content = file_get_contents($envFilePath);
    //         foreach ($data as $key => $value) {
    //             $key = strtoupper($key);
    //             // Escape special characters for regex if value contains them
    //             $escapedValue = addslashes($value);
    //             if (str_contains($content, $key . '=')) {
    //                 $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$escapedValue}\"", $content);
    //             } else {
    //                 $content .= "\n{$key}=\"{$escapedValue}\"";
    //             }
    //         }
    //         file_put_contents($envFilePath, $content);
    //         // You might need to clear config cache if .env changes affect cached config values
    //         // Artisan::call('config:cache'); // or Artisan::call('config:clear');
    //     }
    // }
}