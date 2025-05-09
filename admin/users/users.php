<!-- lot-reservation/admin/users/users.php -->
<?php
session_start();
include('../../config/db.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize data arrays
$clients_data = [];
$agents_data = [];

// Fetch client data for modal
$clients_query = "
    SELECT 
        u.user_id, 
        u.email, 
        u.phone AS contact_number, 
        u.address,
        c.client_id, 
        c.firstname, 
        c.lastname, 
        c.middlename 
    FROM user u
    JOIN client c ON u.user_id = c.user_id
    WHERE u.role = 'CLIENT'
";
$clients_result = $conn->query($clients_query);

if ($clients_result === false) {
    die("Error in clients query: " . $conn->error);
} else {
    while ($client = $clients_result->fetch_assoc()) {
        $clients_data[$client['client_id']] = $client;
    }
    $clients_result->free();
}

// Fetch agent data for modal
$agents_query = "
    SELECT 
        u.user_id, 
        u.email, 
        u.phone AS contact_number, 
        u.address,
        a.agent_id, 
        a.firstname, 
        a.lastname, 
        a.middlename, 
        a.license_number 
    FROM user u
    JOIN agent a ON u.user_id = a.user_id
    WHERE u.role = 'AGENT'
";
$agents_result = $conn->query($agents_query);

if ($agents_result === false) {
    die("Error in agents query: " . $conn->error);
} else {
    while ($agent = $agents_result->fetch_assoc()) {
        $agents_data[$agent['agent_id']] = $agent;
    }
    $agents_result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../users/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>
    
    <main class="main-content">
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <section class="user-management">
                <!-- Error message container -->
                <?php if ($conn->error): ?>
                    <div class="alert-query-error">
                        Database error occurred. Please check your queries.
                    </div>
                <?php endif; ?>
                
                <!-- CLIENTS TABLE -->
                <div class="table-section">
                    <h3 class="section-heading">Clients</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($clients_data)): ?>
                            <?php foreach ($clients_data as $client_id => $client): 
                                $fullName = $client['firstname'] . ' ' . ($client['middlename'] ? $client['middlename'] . ' ' : '') . $client['lastname'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($client['email']) ?></td>
                                    <td><?= htmlspecialchars($client['contact_number']) ?></td>
                                    <td>
                                        <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#clientModal" 
                                            data-client-id="<?= $client_id ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No clients found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- AGENTS TABLE -->
                <div class="table-section">
                    <h3 class="section-heading">Agents</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>License #</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($agents_data)): ?>
                            <?php foreach ($agents_data as $agent_id => $agent): 
                                $fullName = $agent['firstname'] . ' ' . ($agent['middlename'] ? $agent['middlename'] . ' ' : '') . $agent['lastname'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($agent['email']) ?></td>
                                    <td><?= htmlspecialchars($agent['license_number']) ?></td>
                                    <td><?= htmlspecialchars($agent['contact_number']) ?></td>
                                    <td>
                                        <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#agentModal" 
                                            data-agent-id="<?= $agent_id ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No agents found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<!-- Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalLabel">Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row user-details">
                    <dt class="col-sm-3">Full Name:</dt>
                    <dd class="col-sm-9" id="client-fullname"></dd>
                    
                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9" id="client-email"></dd>
                    
                    <dt class="col-sm-3">Phone:</dt>
                    <dd class="col-sm-9" id="client-contact"></dd>
                    
                    <dt class="col-sm-3">Address:</dt>
                    <dd class="col-sm-9" id="client-address"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Agent Modal -->
<div class="modal fade" id="agentModal" tabindex="-1" aria-labelledby="agentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentModalLabel">Agent Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row user-details">
                    <dt class="col-sm-3">Full Name:</dt>
                    <dd class="col-sm-9" id="agent-fullname"></dd>
                    
                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9" id="agent-email"></dd>
                    
                    <dt class="col-sm-3">License Number:</dt>
                    <dd class="col-sm-9" id="agent-license"></dd>
                    
                    <dt class="col-sm-3">Phone:</dt>
                    <dd class="col-sm-9" id="agent-contact"></dd>
                    
                    <dt class="col-sm-3">Address:</dt>
                    <dd class="col-sm-9" id="agent-address"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Client modal handler
    const clientModal = document.getElementById('clientModal');
    if (clientModal) {
        clientModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clientId = button.getAttribute('data-client-id');
            const clientData = <?php echo json_encode($clients_data); ?>;
            const client = clientData[clientId];
            
            if (client) {
                document.getElementById('client-fullname').textContent = 
                    `${client.firstname} ${client.middlename ? client.middlename + ' ' : ''}${client.lastname}`;
                document.getElementById('client-email').textContent = client.email || 'N/A';
                document.getElementById('client-contact').textContent = client.contact_number || 'N/A';
                document.getElementById('client-address').textContent = client.address || 'N/A';
            }
        });
    }
    
    // Agent modal handler
    const agentModal = document.getElementById('agentModal');
    if (agentModal) {
        agentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const agentId = button.getAttribute('data-agent-id');
            const agentData = <?php echo json_encode($agents_data); ?>;
            const agent = agentData[agentId];
            
            if (agent) {
                document.getElementById('agent-fullname').textContent = 
                    `${agent.firstname} ${agent.middlename ? agent.middlename + ' ' : ''}${agent.lastname}`;
                document.getElementById('agent-email').textContent = agent.email || 'N/A';
                document.getElementById('agent-license').textContent = agent.license_number || 'N/A';
                document.getElementById('agent-contact').textContent = agent.contact_number || 'N/A';
                document.getElementById('agent-address').textContent = agent.address || 'N/A';
            }
        });
    }
</script>
</body>
</html>