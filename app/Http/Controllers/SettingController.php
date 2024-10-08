<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

       /**
     * @OA\Post(
     *     path="/api/settings/update",
     *     tags={"Settings"},
     *     summary="Update settings",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="pagination_logement_acceuil", type="integer"),
     *                 @OA\Property(property="condition_tranche_paiement", type="string"),
     *                 @OA\Property(property="condition_prix_logement", type="string"),
     *                 @OA\Property(property="condition_sponsoring_logement", type="string"),
     *                 @OA\Property(property="contact_email", type="string"),
     *                 @OA\Property(property="contact_telephone", type="string"),
     *                 @OA\Property(property="facebook_url", type="string", format="url"),
     *                 @OA\Property(property="twitter_url", type="string", format="url"),
     *                 @OA\Property(property="instagram_url", type="string", format="url"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url"),
*                 @OA\Property(
 *                     property="logo",
 *                     type="array",
 *                     @OA\Items(
 *                         type="string",
 *                         format="binary",
 *                         description="The logo image files to upload"
 *                     )
 *                 ),
     *                 @OA\Property(
     *                     property="app_mode",
     *                     type="string",
     *                     enum={"PRODUCTION", "DEVELOPPEMENT"}
     *                 ),
     *                 @OA\Property(property="adresse_serveur_fichier", type="string"),
     *                 @OA\Property(property="montant_maximum_recharge", type="number", format="float"),
     *                 @OA\Property(property="montant_minimum_recharge", type="number", format="float"),
     *                 @OA\Property(property="montant_minimum_retrait", type="number", format="float"),
     *                 @OA\Property(property="montant_maximum_retrait", type="number", format="float"),
     *                 @OA\Property(property="montant_minimum_solde_retrait", type="number", format="float" ),
     *                 @OA\Property(property="commission_partenaire_defaut", type="number", format="float" ),
     *                 @OA\Property(property="reduction_partenaire_defaut", type="number", format="float" ),
     *                 @OA\Property(property="number_of_reservation_partenaire_defaut", type="integer"),
     *                 @OA\Property(property="commission_hote_defaut", type="number", format="float"),
     *                @OA\Property(property="max_night_number", type="number", format="integer"),
     *                @OA\Property(property="max_value_reduction", type="number", format="float"),
     *                @OA\Property(property="max_number_of_reservation", type="number", format="integer"),
     *                @OA\Property(property="max_value_promotion", type="number", format="float"),
     *                @OA\Property(property="commission_seuil_hote_partenaire", type="number", format="float"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings updated successfully."),
     *             @OA\Property(property="setting", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        // Check if settings exist, otherwise create a new one
        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create([
                'pagination_logement_acceuil' => 10,
                'condition_tranche_paiement' => '',
                'condition_prix_logement' => '',
                'condition_sponsoring_logement' => '',
                'contact_email' => '',
                'contact_telephone' => '',
                'facebook_url' => '',
                'twitter_url' => '',
                'instagram_url' => '',
                'linkedin_url' => '',
                'logo' => '',
                'app_mode' => '',
                'adresse_serveur_fichier' => '',
                'commission_partenaire_defaut' => '',
                'reduction_partenaire_defaut' => '',
                'number_of_reservation_partenaire_defaut' => '',
                'commission_hote_defaut' => '',
                'max_night_number'  => '',
                'max_value_reduction' => '',
                'max_number_of_reservation' => '',
                'max_value_promotion' => '',
                'commission_seuil_hote_partenaire' => ''
            ]);
        }

        $validationResult = $this->validateSettings($request);

        if ($validationResult['fails']) {
            return (new ServiceController())->apiResponse(404, [], $validationResult['errors']);
        }

        $validatedData = $validationResult['data'];

        $this->updateFields($settings, $validatedData);

        if ($request->hasFile('logo')) {
            if(is_array($request->file('logo'))){
                if (isset($request->file('logo')[0])){
                    $validationResultFile = $this->fileService->uploadFiles($request->file('logo')[0], 'image/logos','extensionImage');

                    if ($validationResultFile['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $validationResultFile['result']);
                    }
                    
                    
                    $settings->logo = $validationResultFile['result'];
                }else{
                    return (new ServiceController())->apiResponse(404, [], 'Aucune image trouvé dans les données.');
                }
            }
        }

        if($request->has('reduction_partenaire_defaut')){
            if(!is_null(Setting::first()->commission_seuil_hote_partenaire)){
                if(!is_null(Setting::first()->reduction_partenaire_defaut)){
                    if($request->input('reduction_partenaire_defaut') >= Setting::first()->commission_seuil_hote_partenaire){
                        return (new ServiceController())->apiResponse(404,[], "La valeur de réduction partenaire par défaut ne doit pas dépasser ".Setting::first()->commission_seuil_hote_partenaire);
                    }
                }
               
            }
        }

        if($request->has('commission_hote_defaut')){
            if(!is_null(Setting::first()->commission_seuil_hote_partenaire)){
                if(!is_null(Setting::first()->commission_hote_defaut)){
                    if(!is_null($request->input('commission_hote_defaut')) and $request->input('commission_hote_defaut') <= Setting::first()->commission_seuil_hote_partenaire){
                        return (new ServiceController())->apiResponse(404,[], "La valeur de commission hôte par défaut ne doit pas être en dessous de ".Setting::first()->commission_seuil_hote_partenaire);
                    }
                }
               
            }
        }

        $settings->save();
        $data = ["setting" => $settings];

        return (new ServiceController())->apiResponse(200, $data, 'Modification effectuée avec succès');
    }

    /**
     * @OA\Get(
     *     path="/api/settings/index",
     *     tags={"Settings"},
     *     summary="Get all settings",
     *     @OA\Response(
     *         response=200,
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings retrieved successfully."),
     *             @OA\Property(property="setting", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Settings not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Settings not found.")
     *         )
     *     )
     * )
     */
    public function show()
    {
        $settings = Setting::first();

        if (!$settings) {
            return (new ServiceController())->apiResponse(404, [], "Aucun enregistrement trouvé");
        }

        $data = ["setting" => $settings];

        return (new ServiceController())->apiResponse(200, $data, 'ok');
    }

    private function validateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pagination_logement_acceuil' => 'nullable|integer',
            'condition_tranche_paiement' => 'nullable|string',
            'condition_prix_logement' => 'nullable|string',
            'condition_sponsoring_logement' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_telephone' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'app_mode' => 'nullable|string|in:PRODUCTION,DEVELOPPEMENT',
            'adresse_serveur_fichier' => 'nullable|url',
            'montant_maximum_recharge' => 'nullable|numeric|min:1',
            'montant_minimum_recharge' => 'nullable|numeric|min:1',
            'montant_minimum_retrait' => 'nullable|numeric|min:1',
            'montant_maximum_retrait' => 'nullable|numeric|min:1',
            'montant_minimum_solde_retrait' => 'nullable|numeric|min:1',
            'logo' =>'nullable',
            'commission_partenaire_defaut' => 'nullable|integer|between:0,100',
            'reduction_partenaire_defaut' => 'nullable|numeric|between:0,100',
            'number_of_reservation_partenaire_defaut' => 'nullable|integer|min:1',
            'commission_hote_defaut' => 'nullable|numeric|between:0,100',
            'max_night_number'  => 'nullable|integer|min:1',
            'max_value_reduction' => 'nullable|numeric|between:0,100',
            'max_number_of_reservation' => 'nullable|integer|min:1',
            'max_value_promotion' => 'nullable|numeric|between:0,100',
             'commission_seuil_hote_partenaire' => 'nullable|numeric|between:0,100'
        ]);

        if ($validator->fails()) {
            return [
                'fails' => true,
                'errors' => $validator->errors()
            ];
        }

        return [
            'fails' => false,
            'data' => $validator->validated()
        ];
    }

    private function updateFields(Setting $settings, array $validatedData)
    {
        foreach ($validatedData as $field => $value) {
            if (array_key_exists($field, $validatedData) && !empty($value)) {
                $settings->$field = $value;
            }
        }
    }
}
