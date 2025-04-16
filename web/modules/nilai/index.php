<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

$auth = new AuthHelper();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    redirect('/web/index.php', 'Please login first.', 'danger');
}

$page_title = "Grade Management";
$db = (new Database())->getConnection();
$user = $auth->getCurrentUser();

// Get user-specific data
if ($user['peran'] === 'siswa') {
    // Get student's grades
    $stmt = $db->prepare("
        SELECT n.*, mp.nama as mata_pelajaran_nama, p.nama as guru_nama,
               t.judul as tugas_judul, pt.dikumpulkan_pada,
               jn.nama as jenis_penilaian_nama, jn.bobot
        FROM nilai n
        JOIN pengumpulan_tugas pt ON n.pengumpulan_tugas_id = pt.id
        JOIN tugas t ON pt.tugas_id = t.id
        JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id
        JOIN guru g ON n.dinilai_oleh = g.id
        JOIN pengguna p ON g.pengguna_id = p.id
        JOIN jenis_penilaian jn ON t.jenis_penilaian_id = jn.id
        WHERE n.siswa_id = (SELECT id FROM siswa WHERE pengguna_id = ?)
        ORDER BY n.dibuat_pada DESC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get grade recap
    $stmt = $db->prepare("
        SELECT rn.*, mp.nama as mata_pelajaran_nama
        FROM rekap_nilai rn
        JOIN mata_pelajaran mp ON rn.mata_pelajaran_id = mp.id
        WHERE rn.siswa_id = (SELECT id FROM siswa WHERE pengguna_id = ?)
        ORDER BY rn.semester DESC, mp.nama ASC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $recap = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} elseif ($user['peran'] === 'guru') {
    // Get teacher's given grades
    $stmt = $db->prepare("
        SELECT n.*, mp.nama as mata_pelajaran_nama, 
               CONCAT(s.nis, ' - ', ps.nama) as siswa_nama,
               t.judul as tugas_judul, pt.dikumpulkan_pada,
               jn.nama as jenis_penilaian_nama, jn.bobot
        FROM nilai n
        JOIN pengumpulan_tugas pt ON n.pengumpulan_tugas_id = pt.id
        JOIN tugas t ON pt.tugas_id = t.id
        JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id
        JOIN siswa s ON n.siswa_id = s.id
        JOIN pengguna ps ON s.pengguna_id = ps.id
        JOIN jenis_penilaian jn ON t.jenis_penilaian_id = jn.id
        WHERE n.dinilai_oleh = (SELECT id FROM guru WHERE pengguna_id = ?)
        ORDER BY n.dibuat_pada DESC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} else {
    // Admin can see all grades
    $grades = $db->query("
        SELECT n.*, mp.nama as mata_pelajaran_nama, 
               CONCAT(s.nis, ' - ', ps.nama) as siswa_nama,
               CONCAT(g.nip, ' - ', pg.nama) as guru_nama,
               t.judul as tugas_judul, pt.dikumpulkan_pada,
               jn.nama as jenis_penilaian_nama, jn.bobot
        FROM nilai n
        JOIN pengumpulan_tugas pt ON n.pengumpulan_tugas_id = pt.id
        JOIN tugas t ON pt.tugas_id = t.id
        JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id
        JOIN siswa s ON n.siswa_id = s.id
        JOIN pengguna ps ON s.pengguna_id = ps.id
        JOIN guru g ON n.dinilai_oleh = g.id
        JOIN pengguna pg ON g.pengguna_id = pg.id
        JOIN jenis_penilaian jn ON t.jenis_penilaian_id = jn.id
        ORDER BY n.dibuat_pada DESC")->fetch_all(MYSQLI_ASSOC);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <?php echo $user['peran'] === 'siswa' ? 'My Grades' : 'Grade Management'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($user['peran'] === 'siswa' && !empty($recap)): ?>
                        <!-- Grade Recap for Students -->
                        <h5 class="mb-3">Grade Recap</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Semester</th>
                                        <th>Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recap as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['mata_pelajaran_nama']); ?></td>
                                            <td><?php echo htmlspecialchars($r['semester']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $r['nilai_akhir'] >= 75 ? 'success' : 
                                                        ($r['nilai_akhir'] >= 60 ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $r['nilai_akhir']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Detailed Grades -->
                    <h5 class="mb-3">Detailed Grades</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="gradesTable">
                            <thead>
                                <tr>
                                    <?php if ($user['peran'] !== 'siswa'): ?>
                                        <th>Student</th>
                                    <?php endif; ?>
                                    <th>Subject</th>
                                    <th>Assignment</th>
                                    <th>Type</th>
                                    <th>Weight</th>
                                    <th>Score</th>
                                    <th>Graded By</th>
                                    <th>Graded On</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <?php if ($user['peran'] !== 'siswa'): ?>
                                            <td><?php echo htmlspecialchars($grade['siswa_nama']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($grade['mata_pelajaran_nama']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['tugas_judul']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['jenis_penilaian_nama']); ?></td>
                                        <td><?php echo $grade['bobot']; ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $grade['skor'] >= 75 ? 'success' : 
                                                    ($grade['skor'] >= 60 ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo $grade['skor']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($grade['guru_nama']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($grade['dinilai_pada'])); ?></td>
                                        <td>
                                            <?php if (!empty($grade['komentar_guru'])): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        data-bs-toggle="tooltip" 
                                                        title="<?php echo htmlspecialchars($grade['komentar_guru']); ?>">
                                                    <i class="fas fa-comment"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#gradesTable').DataTable({
        "order": [[7, "desc"]], // Sort by graded date by default
        "pageLength": 25,
        "language": {
            "search": "Search grades:"
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
