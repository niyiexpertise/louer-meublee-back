$seller = new sellers();
$seller->name = $user->name;
$seller->email = $user->email;
$seller->code_pays = $user->code_pays;
$seller->rib = $user->rib;
$seller->password = bcrypt($user->password);
$seller->location = $user->location;
$seller->number_phone = $user->number_phone;
$seller->save();
$seller->assignRole('seller');
$commission=new Commission();
$commission->seller_id=$seller->id;
$commission->valeur=5;
$commission->save();
$verification = new verification_statues();
$verification->status = 'unverified';
$verification->seller_id = $seller->id;
$verification->save();

INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES 
8('manageHousingType', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
9('manageType', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
10('manageRole', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
11('manageCriteria', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
12('manageEquipment', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
13('manageHousing', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
14('manageUser', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
15('managePermission', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
16('manageReview', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
17('manageLanguage', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
18('manageCategory', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
19('managePreference', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
20('managePropertyType', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
21('manageUsers', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
22('manageDocument', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
23('manageVerificationDocument', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
24('manageCommission', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z'),
25('manageLogement', 'web', '2024-04-11T11:29:15.000000Z', '2024-04-11T11:29:15.000000Z');
26 manageReservation



          $request->validate([
            'photo' => 'image|mimes:jpeg,jpg,png,gif'
          ]);
        $baseURL = $request->getSchemeAndHttpHost();

        $imagepathSeller = $request->file('photo')->store('photoSeller', 'public');
        sellers::where('id',Auth::id())->update(['photo' =>$baseURL . '/storage/' . $imagepathSeller]);



        INSERT INTO `users` (`id`, `lastname`, `firstname`, `email`, `code_pays`, `telephone`, `country`, `file_profil`, `piece_of_identity`, `city`, `address`, `sexe`, `postal_code`, `is_admin`, `is_traveller`, `is_hote`, `icone`, `is_deleted`, `is_blocked`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (NULL, 'naruto', 'uzumaki', 'admin@gmail.com', '+229', '84242287', 'Benin', ' http://127.0.0.1:8000/image/photo_profil/661f851981dde.jpg', NULL, 'cotonou', 'sf', 'masculin', 'zgre', '0', '1', '0', NULL, '0', '0', NULL, '$2y$12$kdgx4ZYsVQw7nwMXMGlnZ.8H7A9W9u3uaSHNTC4xhGJxWc3fD1LR6', NULL, NULL, NULL);

          INSERT INTO `housings` (`id`, `housing_type_id`, `property_type_id`, `user_id`, `name`, `description`, `number_of_bed`, `number_of_traveller`, `sit_geo_lat`, `sit_geo_lng`, `country`, `address`, `city`, `department`, `is_camera`, `is_accepted_animal`, `is_animal_exist`, `is_disponible`, `interior_regulation`, `telephone`, `code_pays`, `status`, `arrived_independently`, `icone`, `is_instant_reservation`, `maximum_duration`, `minimum_duration`, `time_before_reservation`, `cancelation_condition`, `departure_instruction`, `is_deleted`, `is_blocked`, `created_at`, `updated_at`) VALUES (NULL, '3', '3', '1', 'maison Otaku', 'accueillante ...', '4', '5', '4.54', '5.524', 'Benin', 'Konoha', 'Cotonou', 'Littoral', '1', '1', '1', '1', 'règlement intérieur', '98557852', '+229', 'verified', 'instruction d\'arrivé', NULL, '1', '5', '14', NULL, 'Condition d\'annulation', 'instruction de départ', '0', '0', NULL, NULL);

          INSERT INTO `reservations` (`id`, `user_id`, `housing_id`, `date_of_reservation`, `date_of_starting`, `date_of_end`, `number_of_adult`, `number_of_child`, `number_of_domestical_animal`, `number_of_baby`, `icone`, `created_at`, `updated_at`, `is_deleted`, `is_blocked`, `message_to_hote`, `code_pays`, `telephone_traveler`, `photo`, `heure_arrivee_max`, `heure_arrivee_min`, `is_tranche_paiement`, `montant_total`, `valeur_payee`, `is_confirmed_hote`, `is_integration`, `is_rejected_traveler`, `is_rejected_hote`, `motif_rejet_hote`, `motif_rejet_traveler`) VALUES (NULL, '2', '3', '2024-04-22', '2024-04-23', '2024-04-24', '4', '2', '0', '2', NULL, '', '', '0', '0', NULL, NULL, NULL, NULL, '23:59:47', '12:49:47', '0', '584242400', '8900000', '0', '0', '0', '0', NULL, NULL);