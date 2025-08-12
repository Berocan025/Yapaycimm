<?php
/**
 * DiziPortal.Com - Box Sports Matches API
 * Developer: DiziPortal.Com Development Team
 * Simple JSON-based matches management system
 */

// Set CORS headers first
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 3600');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type after CORS
header('Content-Type: application/json; charset=utf-8');

$dataFile = 'matches.json';
$defaultMatches = [
    [
        'id' => 'beinsports1',
        'name' => 'BeIN Sports 1 HD - Canlı Yayın',
        'time' => 'Canlı',
        'homeTeam' => 'BeIN Sports',
        'awayTeam' => 'HD Yayın',
        'homeTeamLogo' => 'https://via.placeholder.com/30x30/dc2626/ffffff?text=BS',
        'awayTeamLogo' => 'https://via.placeholder.com/30x30/ef4444/ffffff?text=HD',
        'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
        'createdAt' => date('c')
    ],
    [
        'id' => 'demo1',
        'name' => 'Galatasaray vs Fenerbahçe',
        'time' => '20:00',
        'homeTeam' => 'Galatasaray',
        'awayTeam' => 'Fenerbahçe',
        'homeTeamLogo' => 'https://via.placeholder.com/30x30/dc2626/ffffff?text=GS',
        'awayTeamLogo' => 'https://via.placeholder.com/30x30/1a237e/ffffff?text=FB',
        'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
        'createdAt' => date('c')
    ],
    [
        'id' => 'demo2',
        'name' => 'Barcelona vs Real Madrid',
        'time' => '22:00',
        'homeTeam' => 'Barcelona',
        'awayTeam' => 'Real Madrid',
        'homeTeamLogo' => 'https://via.placeholder.com/30x30/dc2626/ffffff?text=BAR',
        'awayTeamLogo' => 'https://via.placeholder.com/30x30/ffffff/000000?text=RM',
        'hls' => 'https://andro.yangin1yerihep2sayende.cfd/checklist/androstreamlivebs1.m3u8',
        'createdAt' => date('c')
    ]
];

/**
 * DiziPortal.Com - Load matches from JSON file
 */
function diziportalLoadMatches($dataFile, $defaultMatches) {
    if (!file_exists($dataFile)) {
        diziportalSaveMatches($dataFile, $defaultMatches);
        return $defaultMatches;
    }
    
    $content = file_get_contents($dataFile);
    $matches = json_decode($content, true);
    
    if (!$matches || !is_array($matches)) {
        diziportalSaveMatches($dataFile, $defaultMatches);
        return $defaultMatches;
    }
    
    return $matches;
}

/**
 * DiziPortal.Com - Save matches to JSON file
 */
function diziportalSaveMatches($dataFile, $matches) {
    $jsonData = json_encode($matches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($dataFile, $jsonData) !== false;
}

/**
 * DiziPortal.Com - Generate unique match ID
 */
function diziportalGenerateId() {
    return 'match_' . time() . '_' . substr(md5(random_bytes(16)), 0, 8);
}

/**
 * DiziPortal.Com - Validate match data
 */
function diziportalValidateMatch($match) {
    return isset($match['name']) && !empty(trim($match['name'])) &&
           isset($match['time']) && !empty(trim($match['time'])) &&
           isset($match['hls']);
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$matches = diziportalLoadMatches($dataFile, $defaultMatches);

switch ($method) {
    case 'GET':
        // DiziPortal.Com - Get all matches
        echo json_encode([
            'success' => true,
            'data' => $matches,
            'timestamp' => time(),
            'developer' => 'DiziPortal.Com'
        ]);
        break;
        
    case 'POST':
        // DiziPortal.Com - Add new match
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !diziportalValidateMatch($input)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid match data',
                'developer' => 'DiziPortal.Com'
            ]);
            break;
        }
        
        $newMatch = [
            'id' => diziportalGenerateId(),
            'name' => trim($input['name']),
            'time' => trim($input['time']),
            'hls' => trim($input['hls']),
            'createdAt' => date('c')
        ];
        
        $matches[] = $newMatch;
        
        if (diziportalSaveMatches($dataFile, $matches)) {
            echo json_encode([
                'success' => true,
                'data' => $newMatch,
                'message' => 'Match added successfully',
                'developer' => 'DiziPortal.Com'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save match',
                'developer' => 'DiziPortal.Com'
            ]);
        }
        break;
        
    case 'DELETE':
        // DiziPortal.Com - Delete match
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Match ID required',
                'developer' => 'DiziPortal.Com'
            ]);
            break;
        }
        
        $matchId = $input['id'];
        $originalCount = count($matches);
        $matches = array_filter($matches, function($match) use ($matchId) {
            return $match['id'] !== $matchId;
        });
        $matches = array_values($matches); // Reindex array
        
        if (count($matches) < $originalCount) {
            if (diziportalSaveMatches($dataFile, $matches)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Match deleted successfully',
                    'developer' => 'DiziPortal.Com'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to save changes',
                    'developer' => 'DiziPortal.Com'
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Match not found',
                'developer' => 'DiziPortal.Com'
            ]);
        }
        break;
        
    case 'PUT':
        // DiziPortal.Com - Clear all matches (admin function)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action']) && $input['action'] === 'clear_all') {
            if (diziportalSaveMatches($dataFile, $defaultMatches)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'All matches cleared, default matches restored',
                    'data' => $defaultMatches,
                    'developer' => 'DiziPortal.Com'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to clear matches',
                    'developer' => 'DiziPortal.Com'
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'developer' => 'DiziPortal.Com'
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed',
            'developer' => 'DiziPortal.Com'
        ]);
        break;
}
?>