<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
            // 
            // Drop the old foreign key constraint
            $table->dropForeign(['bon_commande_id']);
            $table->dropForeign(['point_de_vente_id']);
            $table->dropForeign(['fournisseur_id']);

            // Modify the column if needed (e.g., change data type)
            $table->foreignId('_bon_commande_id')->nullable()->constrained('bon_commandes')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreignId('_point_de_vente_id')->nullable()->constrained('point_de_ventes')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreignId('_fournisseur_id')->nullable()->constrained('fournisseurs')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
           
            // Drop the old foreign key constraint
            // $table->dropForeign(['bon_commande_id']);
            // $table->dropForeign(['point_de_vente_id']);
            // $table->dropForeign(['fournisseur_id']);

            // Modify the column if needed (e.g., change data type)
            $table->foreignId('_bon_commande_id')->nullable()->constrained('bon_commandes')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreignId('_point_de_vente_id')->nullable()->constrained('point_de_ventes')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->foreignId('_fournisseur_id')->nullable()->constrained('fournisseurs')
                ->onDelete("CASCADE")->onUpdate("CASCADE");
        });
    }
};
