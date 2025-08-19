# GDPR Compliance Recipe

This recipe installs and configures the GDPR (General Data Protection Regulation) modules needed for database dump obfuscation and compliance.

## What this recipe provides

- **GDPR Core Module**: Base GDPR functionality with compliance checklist
- **Anonymizer Module**: Core anonymization functionality for data obfuscation
- **GDPR Dump Module**: Drush commands for creating obfuscated database dumps
- **Table Mapping Configuration**: Pre-configured table mapping for common user data anonymization

## Modules installed

- `gdpr` - Base GDPR compliance module with checklist
- `anonymizer` - Core anonymization functionality
- `gdpr_dump` - Obfuscated database dump functionality
- `checklistapi` - Checklist API required by GDPR module

## Configuration included

### Table Mapping (`gdpr_dump.table_map.yml`)

The recipe includes pre-configured anonymization mapping for:

**comment_field_data**:
- `name`: Uses username anonymizer
- `mail`: Uses email anonymizer

**users_field_data**:
- `name`: Uses text anonymizer  
- `pass`: Uses password anonymizer
- `mail`: Uses email anonymizer

**user__user_picture**:
- `user_picture_alt`: Uses text anonymizer
- `user_picture_title`: Uses text anonymizer

## Usage

### Applying the recipe

```bash
ddev drush recipe gdpr
```

### Using GDPR Dump

After installation, you can create obfuscated database dumps using:

```bash
# Create a GDPR-compliant database dump
ddev drush gdpr:sql:dump --result-file=obfuscated-dump.sql

# Create dump with specific structure-only tables
ddev drush gdpr:sql:dump --structure-tables-list="cache_*,sessions" --result-file=dump.sql

# Use with existing drush site aliases for structure tables
ddev drush gdpr:sql:dump --structure-tables-key=common --result-file=dump.sql
```

### Configuration Management

The GDPR dump table mapping can be managed through:

1. **Web UI**: Visit `/admin/config/gdpr/gdpr-dump` to configure table mappings
2. **Configuration files**: Modify `gdpr_dump.table_map.yml` in your configuration
3. **Drush**: Use `drush cset gdpr_dump.table_map` commands

### Adding more anonymization

To add more tables or fields for anonymization:

1. Go to `/admin/config/gdpr/gdpr-dump`
2. Select additional tables and configure anonymization plugins for specific fields
3. Export configuration: `drush cex`

## Security Benefits

- **Data Protection**: Sensitive user data is anonymized in database dumps
- **GDPR Compliance**: Helps meet GDPR requirements for data protection
- **Safe Development**: Development/staging environments can use obfuscated production data
- **Audit Trail**: GDPR module provides compliance checklist and tracking

## Related Configuration

This recipe works well with:
- Structure tables configuration in drush site aliases
- Environment-specific database dump strategies
- CI/CD pipelines requiring obfuscated data