<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    /**
     * Display a listing of all reservations (for staff/admin).
     */
    public function index(Request $request): JsonResponse
    {
        $reservations = Reservation::with(['car', 'location', 'driver', 'payment', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'user' => [
                        'name' => $reservation->user->name,
                        'email' => $reservation->user->email,
                        'phone' => $reservation->user->phone
                    ],
                    'car' => $reservation->car ? [
                        'brand' => $reservation->car->brand,
                        'model' => $reservation->car->model,
                        'image' => $reservation->car->image,
                    ] : null,
                    'car_type' => $reservation->car_type,
                    'total_price' => $reservation->totalPrice,
                    'status' => $reservation->status,
                    'pickup_location' => $reservation->pickup_location,
                    'dropoff_location' => $reservation->dropoff_location,
                    'pickup_date' => $reservation->startDate,
                    'dropoff_date' => $reservation->endDate,
                    'selectDriver' => $reservation->selectDriver,
                    'created_at' => $reservation->created_at,
                    
                    'driver' => $reservation->driver ? [
                        'name' => $reservation->driver->name
                    ] : null,
                    'payment' => $reservation->payment ? [
                        'id' => $reservation->payment->id,
                        'status' => $reservation->payment->status
                    ] : null
                ];
            });
        
        return response()->json($reservations);
    }
    
    /**
     * Display a listing of the user's bookings.
     */
    public function getUserReservations(Request $request): JsonResponse
    {
        $reservations = $request->user()->reservations()
            ->with(['car', 'location', 'driver', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'user' => [
                        'name' => $reservation->user->name,
                        'email' => $reservation->user->email,
                        'phone' => $reservation->user->phone
                    ],
                    'car' => $reservation->car ? [
                        'brand' => $reservation->car->brand,
                        'model' => $reservation->car->model,
                        'image' => $reservation->car->image,
                    ] : null,
                    'car_type' => $reservation->car_type,
                    'total_price' => $reservation->totalPrice,
                    'status' => $reservation->status,
                    'pickup_location' => $reservation->pickup_location,
                    'dropoff_location' => $reservation->dropoff_location,
                    'selectDriver' => $reservation->selectDriver,
                    'pickup_date' => $reservation->startDate,
                    'dropoff_date' => $reservation->endDate,
                    'created_at' => $reservation->created_at,
                    'driver' => $reservation->driver ? [
                        'name' => $reservation->driver->name
                    ] : null,
                    'payment' => $reservation->payment ? [
                        'id' => $reservation->payment->id,
                        'status' => $reservation->payment->status
                    ] : null
                ];
            });
        
        return response()->json($reservations);
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|integer|exists:locations,id',
            'dropoff_location' => 'required|integer|exists:locations,id',
            'pickup_date' => 'required|date',
            'dropoff_date' => 'required|date|after:pickup_date',
            'car_type' => 'required|string',

            'total_cost' => 'required|numeric|min:0',
            'vehicle_id' => 'required|integer|exists:cars,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate rental duration
        $startDate = new \DateTime($request->pickup_date);
        $endDate = new \DateTime($request->dropoff_date);
        $duration = $startDate->diff($endDate)->days + 1;

        // Create reservation
        $reservation = new Reservation([
            'user_id' => $request->user()->id,
            'startDate' => $request->pickup_date,
            'endDate' => $request->dropoff_date,
            'totalPrice' => $request->total_cost,
            'status' => 'pending',
            'selectDriver' => $request->driver ? true : false,
            'statusUpdatedBy' => $request->user()->id,
            'vehicle_id' => $request->vehicle_id,
            'location_id' => $request->pickup_location,
            'payment_id' => 1
        ]);

        $reservation->save();

        return response()->json([
            'message' => 'Reservation created successfully',
            'reservation' => $reservation
        ], 201);
    }
    public function update(Request $request, $id)
    {
        // Get the reservation
        $reservation = Reservation::findOrFail($id);

        // Validate that the user has permission to update this reservation
        if ($request->user()->role !== 'admin' && $reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'statusUpdatedBy' => 'nullable|integer|exists:users,id',
        ]);

        // If statusUpdatedBy is not provided, use the current user's ID
        if (!$request->has('statusUpdatedBy')) {
            $request->merge(['statusUpdatedBy' => $request->user()->id]);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the reservation
        $reservation->update($request->all());
        
        return response()->json($reservation);
    }
    /**
     * Display the specified booking.
     */
    public function show(Request $request, $id)
    {
        $reservation = $request->user()->reservations()->findOrFail($id);
        return response()->json($reservation);
    }

    /**
     * Assign a driver to a reservation.
     */
    public function assignDriver(Request $request, $id)
    {
        try {
            // Find the reservation
            $reservation = Reservation::with(['driver', 'user'])
                ->findOrFail($id);

            // Log debug information
            \Log::info('Assigning driver to reservation', [
                'reservation_id' => $id,
                'driver_id' => $request->driverId,
                'reservation' => $reservation->toArray(),
                'user' => $request->user()->toArray()
            ]);

            // Only admin users can assign drivers
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized',
                    'user' => $request->user()
                ], 403);
            }

            // Only reservations with selectDriver = 1 can be assigned a driver
            if (!$reservation->selectDriver) {
                return response()->json([
                    'message' => 'Driver assignment not allowed for this reservation',
                    'reservation' => $reservation,
                    'current_driver' => $reservation->driver,
                    'user' => $reservation->user
                ], 400);
            }

            // Validate the driver ID
            $validator = Validator::make($request->all(), [
                'driverId' => 'required|integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'input' => $request->all()
                ], 422);
            }

            // Get the driver from users table
            $driver = User::where('id', $request->driverId)
                ->where('role', 'driver')
                ->first();

            if (!$driver) {
                return response()->json([
                    'message' => 'Driver not found',
                    'driverId' => $request->driverId,
                    'available_drivers' => User::where('role', 'driver')->get()
                ], 404);
            }

            // Update the reservation with driver ID
            \Log::info('Updating reservation with driver', [
                'reservation_id' => $id,
                'driver_id' => $driver->id,
                'current_driver_id' => $reservation->driver_id
            ]);

            $reservation->driver_id = $driver->id;
            $reservation->save();

            // Load relationships for the response
            $reservation->load(['driver', 'user']);

            return response()->json([
                'message' => 'Driver assigned successfully',
                'reservation' => $reservation,
                'driver' => $driver
            ], 200);

        } catch (\Exception $e) {
            // Log the error with more details
            \Log::error('Error assigning driver', [
                'error' => $e->getMessage(),
                'reservationId' => $id,
                'driverId' => $request->driverId,
                'stack' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return a more detailed error response
            return response()->json([
                'message' => 'Error assigning driver',
                'error' => $e->getMessage(),
                'reservationId' => $id,
                'driverId' => $request->driverId,
                'stack' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Cancel/delete the specified booking.
     */
    public function destroy(Request $request, $id)
    {
        $reservation = $request->user()->reservations()->findOrFail($id);
        
        // Update status to cancelled instead of deleting
        $reservation->status = 'cancelled';
        $reservation->save();
        
        return response()->json([
            'message' => 'Reservation cancelled successfully'
        ]);
    }
    /**
     * Generate and download reservation details as PDF
     */
    public function downloadPdf(Request $request, $reservationId)
    {
        try {
            // Get the reservation with relationships
            $reservation = Reservation::with(['car', 'user'])->find($reservationId);
            if (!$reservation) {
                \Log::warning('Attempted to access non-existent reservation', [
                    'reservation_id' => $reservationId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'error' => 'Reservation not found',
                    'message' => 'The requested reservation does not exist.'
                ], 404);
            }

            // Prepare data for PDF
            $data = [
                'customerInfo' => [
                    'fullName' => $reservation->user ? $reservation->user->name : 'N/A',
                    'email' => $reservation->user ? $reservation->user->email : 'N/A',
                    'phone' => $reservation->user ? $reservation->user->phone : 'N/A',
                    'age' => $reservation->user ? $reservation->user->age : 'N/A',
                    'driverLicense' => $reservation->user ? $reservation->user->driverLicense : 'N/A'
                ],
                'car' => [
                    'brand' => $reservation->car ? $reservation->car->brand : 'N/A',
                    'model' => $reservation->car ? $reservation->car->model : 'N/A'
                ],
                'pickupLocation' => $reservation->pickup_location,
                'returnLocation' => $reservation->dropoff_location,
                'pickupDate' => $reservation->startDate,
                'pickupTime' => $reservation->pickupTime,
                'returnDate' => $reservation->endDate,
                'returnTime' => $reservation->returnTime,
                'driver' => $reservation->selectDriver ? 'with_driver' : 'self',
                'accessories' => $reservation->accessories ?? [],
                'insurance' => $reservation->insurance ?? 'None',
                'totalCost' => $reservation->totalPrice ?? 0
            ];

            // Generate PDF
            $pdf = Pdf::loadView('reservation_pdf', ['data' => $data]);
            
            // Set appropriate headers for PDF download
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="reservation-' . $reservationId . '.pdf"',
            ];

            return response($pdf->output(), 200, $headers);

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => 'An error occurred while generating the PDF. Please try again later.'
            ], 500);
        }
    }
}