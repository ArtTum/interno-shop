<strong>{{ $translation['front_bank_details_title'] ?? '{front_bank_details_title}' }}</strong> <br>
<strong>{{ $accountInfo['account_name'] ?? '' }}</strong> <br>
{{ $translation['front_bank_details_bank_name'] ?? '{front_bank_details_bank_name}' }}: <strong>{{ $accountInfo['bank_name'] ?? '' }}</strong> <br>
{{ $translation['front_bank_details_account_number'] ?? '{front_bank_details_account_number}' }}: <strong>{{ $accountInfo['account_number'] ?? '' }}</strong><br>
{{ $translation['front_bank_details_sort_code'] ?? '{front_bank_details_sort_code}' }}: <strong>{{ $accountInfo['sort_code'] ?? '' }}</strong><br>
{{ $translation['front_bank_details_iban'] ?? '{front_bank_details_iban}' }}: <strong>{{ $accountInfo['iban'] ?? '' }}</strong><br>
{{ $translation['front_bank_details_bic'] ?? '{front_bank_details_bic}' }}: <strong>{{ $accountInfo['bic_swift'] ?? '' }}</strong>
