<?php
/**
 * DiziPortal.Com - Dizi Portal Sports Channels API
 * Developer: DiziPortal.Com Development Team
 * 7/24 Channels management system
 */

// Set CORS headers first
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 3600');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type after CORS
header('Content-Type: application/json; charset=utf-8');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Channels file path
$channelsFile = __DIR__ . '/channels.json';

// Helper functions
function loadChannels() {
    global $channelsFile;
    
    if (!file_exists($channelsFile)) {
        $defaultChannels = [
            [
                'id' => generateUniqueId(),
                'name' => 'BeIN Sports 1 HD',
                'category' => 'Spor',
                'logo' => 'https://via.placeholder.com/40x40/dc2626/ffffff?text=BS1',
                'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
                'active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => generateUniqueId(),
                'name' => 'BeIN Sports 2 HD',
                'category' => 'Spor',
                'logo' => 'https://via.placeholder.com/40x40/dc2626/ffffff?text=BS2',
                'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
                'active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => generateUniqueId(),
                'name' => 'TRT Spor HD',
                'category' => 'Spor',
                'logo' => 'https://via.placeholder.com/40x40/dc2626/ffffff?text=TRT',
                'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
                'active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        saveChannels($defaultChannels);
        return $defaultChannels;
    }
    
    $content = file_get_contents($channelsFile);
    $channels = json_decode($content, true);
    
    return is_array($channels) ? $channels : [];
}

function saveChannels($channels) {
    global $channelsFile;
    
    // Ensure directory exists
    $directory = dirname($channelsFile);
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    $result = file_put_contents($channelsFile, json_encode($channels, JSON_PRETTY_PRINT));
    return $result !== false;
}

function generateUniqueId() {
    return 'ch_' . uniqid() . '_' . time();
}

function validateChannelData($data) {
    $required = ['name', 'category', 'hls'];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            return "Missing required field: $field";
        }
    }
    
    // Validate URL format
    if (!filter_var($data['hls'], FILTER_VALIDATE_URL)) {
        return "Invalid HLS URL format";
    }
    
    // Validate category
    $validCategories = ['Spor', 'Haber', 'Eğlence', 'Belgesel', 'Müzik'];
    if (!in_array($data['category'], $validCategories)) {
        return "Invalid category. Must be one of: " . implode(', ', $validCategories);
    }
    
    return null;
}

// Handle different HTTP methods
try {
    switch ($method) {
        case 'GET':
            // Get all channels
            $channels = loadChannels();
            
            // Filter only active channels for public API
            if (isset($_GET['active_only']) && $_GET['active_only'] === 'true') {
                $channels = array_filter($channels, function($channel) {
                    return isset($channel['active']) && $channel['active'];
                });
            }
            
            echo json_encode([
                'success' => true,
                'data' => array_values($channels),
                'count' => count($channels),
                'message' => 'Channels loaded successfully'
            ]);
            break;
            
        case 'POST':
            // Add new channel
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate input
            $validationError = validateChannelData($input);
            if ($validationError) {
                throw new Exception($validationError);
            }
            
            // Load existing channels
            $channels = loadChannels();
            
            // Create new channel
            $newChannel = [
                'id' => generateUniqueId(),
                'name' => trim($input['name']),
                'category' => trim($input['category']),
                'logo' => isset($input['logo']) ? trim($input['logo']) : '',
                'hls' => trim($input['hls']),
                'active' => isset($input['active']) ? (bool)$input['active'] : true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add to channels array
            $channels[] = $newChannel;
            
            // Save channels
            if (saveChannels($channels)) {
                echo json_encode([
                    'success' => true,
                    'data' => $newChannel,
                    'message' => 'Channel added successfully'
                ]);
            } else {
                throw new Exception('Failed to save channel');
            }
            break;
            
        case 'DELETE':
            // Delete channel
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('Channel ID is required');
            }
            
            $channelId = $input['id'];
            $channels = loadChannels();
            
            // Find and remove channel
            $originalCount = count($channels);
            $channels = array_filter($channels, function($channel) use ($channelId) {
                return $channel['id'] !== $channelId;
            });
            
            if (count($channels) === $originalCount) {
                throw new Exception('Channel not found');
            }
            
            // Save updated channels
            if (saveChannels(array_values($channels))) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Channel deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete channel');
            }
            break;
            
        case 'PUT':
            // Update channel or restore defaults
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                // Restore default channels
                $defaultChannels = [
                    [
                        'id' => generateUniqueId(),
                        'name' => 'BeIN Sports 1 HD',
                        'category' => 'Spor',
                        'logo' => 'https://via.placeholder.com/40x40/dc2626/ffffff?text=BS1',
                        'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
                        'active' => true,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => generateUniqueId(),
                        'name' => 'BeIN Sports 2 HD',
                        'category' => 'Spor',
                        'logo' => 'https://via.placeholder.com/40x40/dc2626/ffffff?text=BS2',
                        'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
                        'active' => true,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
                
                if (saveChannels($defaultChannels)) {
                    echo json_encode([
                        'success' => true,
                        'data' => $defaultChannels,
                        'message' => 'Default channels restored successfully'
                    ]);
                } else {
                    throw new Exception('Failed to restore default channels');
                }
            } else {
                // Update existing channel
                if (!isset($input['id'])) {
                    throw new Exception('Channel ID is required for update');
                }
                
                $channelId = $input['id'];
                $channels = loadChannels();
                
                // Find channel to update
                $channelIndex = -1;
                foreach ($channels as $index => $channel) {
                    if ($channel['id'] === $channelId) {
                        $channelIndex = $index;
                        break;
                    }
                }
                
                if ($channelIndex === -1) {
                    throw new Exception('Channel not found');
                }
                
                // Validate input
                $validationError = validateChannelData($input);
                if ($validationError) {
                    throw new Exception($validationError);
                }
                
                // Update channel
                $channels[$channelIndex] = array_merge($channels[$channelIndex], [
                    'name' => trim($input['name']),
                    'category' => trim($input['category']),
                    'logo' => isset($input['logo']) ? trim($input['logo']) : $channels[$channelIndex]['logo'],
                    'hls' => trim($input['hls']),
                    'active' => isset($input['active']) ? (bool)$input['active'] : $channels[$channelIndex]['active'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Save channels
                if (saveChannels($channels)) {
                    echo json_encode([
                        'success' => true,
                        'data' => $channels[$channelIndex],
                        'message' => 'Channel updated successfully'
                    ]);
                } else {
                    throw new Exception('Failed to update channel');
                }
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Request failed'
    ]);
}
?>