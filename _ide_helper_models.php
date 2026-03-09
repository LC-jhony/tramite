<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $start_period
 * @property string $end_period
 * @property string $mayor
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereEndPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereMayor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereStartPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Administration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $representation
 * @property string|null $full_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $dni
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $ruc
 * @property string|null $company
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static \Database\Factories\CustomerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereRepresentation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereRuc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $customer_id
 * @property string $document_number
 * @property string $case_number
 * @property string $subject
 * @property string $origen
 * @property int $document_type_id
 * @property int $current_office_id
 * @property int $gestion_id
 * @property int|null $user_id
 * @property string|null $folio
 * @property string $reception_date
 * @property string|null $response_deadline
 * @property string|null $condition
 * @property string $status
 * @property int|null $priority_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Administration $administration
 * @property-read \App\Models\Office $currentOffice
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Movement|null $latestMovement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movements
 * @property-read int|null $movements_count
 * @property-read \App\Models\DocumentType $type
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCaseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCurrentOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereGestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrigen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReceptionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereResponseDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentReception> $receptions
 * @property-read int|null $receptions_count
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $document_id
 * @property int $movement_id
 * @property int $user_id
 * @property int $office_id
 * @property string $reception_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Document $document
 * @property-read \App\Models\Movement $movement
 * @property-read \App\Models\Office $office
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\DocumentReceptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereMovementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereReceptionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReception whereUserId($value)
 */
	class DocumentReception extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $requires_response
 * @property int|null $response_days
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereRequiresResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereResponseDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DocumentType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property int|null $from_office_id
 * @property int|null $to_office_id
 * @property string $action
 * @property string|null $indication
 * @property string|null $observation
 * @property string $receipt_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Document $document
 * @property-read \App\Models\Office|null $fromOffice
 * @property-read \App\Models\Office|null $toOffice
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereFromOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereIndication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereObservation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereReceiptDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereToOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereUserId($value)
 * @mixin \Eloquent
 */
	class Movement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movementsFrom
 * @property-read int|null $movements_from_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movementsTo
 * @property-read int|null $movements_to_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Office extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $office_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movements
 * @property-read int|null $movements_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Office|null $office
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

