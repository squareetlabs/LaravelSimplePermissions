<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to entity_permission table
        if (Schema::hasTable('entity_permission')) {
            try {
                Schema::table('entity_permission', function (Blueprint $table) {
                    if (!$this->hasIndex('entity_permission', 'entity_permission_entity_type_entity_id_index')) {
                        $table->index(['entity_type', 'entity_id'], 'entity_permission_entity_type_entity_id_index');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }

        // Add indexes to entity_ability table
        if (Schema::hasTable('entity_ability')) {
            try {
                Schema::table('entity_ability', function (Blueprint $table) {
                    if (!$this->hasIndex('entity_ability', 'entity_ability_entity_type_entity_id_index')) {
                        $table->index(['entity_type', 'entity_id'], 'entity_ability_entity_type_entity_id_index');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }

        // Add indexes to permissions table
        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    if (!$this->hasIndex('permissions', 'permissions_code_index')) {
                        $table->index(['code'], 'permissions_code_index');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }

        // Add indexes to groups table
        if (Schema::hasTable('groups')) {
            try {
                Schema::table('groups', function (Blueprint $table) {
                    if (!$this->hasIndex('groups', 'groups_code_index')) {
                        $table->index(['code'], 'groups_code_index');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_permission', function (Blueprint $table) {
            $table->dropIndex('entity_permission_entity_type_entity_id_index');
        });

        Schema::table('entity_ability', function (Blueprint $table) {
            $table->dropIndex('entity_ability_entity_type_entity_id_index');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex('permissions_code_index');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('groups_code_index');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    protected function hasIndex(string $table, string $index): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $connection = Schema::getConnection();
        
        // For SQLite, use a simpler check
        if ($connection->getDriverName() === 'sqlite') {
            $indexes = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?", [$table, $index]);
            return !empty($indexes);
        }

        // For other databases, try Doctrine if available
        try {
            if (method_exists($connection, 'getDoctrineSchemaManager')) {
                $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
                $doctrineTable = $doctrineSchemaManager->introspectTable($table);
                return $doctrineTable->hasIndex($index);
            }
        } catch (\Exception $e) {
            // Fallback: assume index doesn't exist if we can't check
            return false;
        }

        return false;
    }
};

