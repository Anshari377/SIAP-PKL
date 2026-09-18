/**
 * Shared utility for Application Status mapping
 * Handles normalized status values:
 * - 'pending' / 'diajukan' -> "Dalam Proses" (badge-warning)
 * - 'accepted' / 'diterima' -> "Diterima" (badge-success)
 * - 'rejected' / 'ditolak' -> "Ditolak" (badge-danger)
 * - 'completed' / 'selesai' -> "Selesai" (badge-neutral)
 */

export const getStatusLabel = (status) => {
    if (!status) return 'Dalam Proses';

    switch (status.toLowerCase()) {
        case 'pending':
        case 'diajukan':
            return 'Dalam Proses';
        case 'accepted':
        case 'diterima':
        case 'berkas_diterima':
        case 'diverifikasi':
            return 'Diterima';
        case 'revision':
        case 'revisi':
            return 'Perlu Revisi';
        case 'rejected':
        case 'ditolak':
            return 'Ditolak';
        case 'completed':
        case 'selesai':
            return 'Selesai';
        default:
            return status;
    }
};

export const getStatusBadgeClass = (status) => {
    if (!status) return 'badge-warning';

    switch (status.toLowerCase()) {
        case 'pending':
        case 'diajukan':
            return 'badge-warning';
        case 'accepted':
        case 'diterima':
        case 'berkas_diterima':
        case 'diverifikasi':
            return 'badge-success';
        case 'revision':
        case 'revisi':
            return 'badge-revision';
        case 'rejected':
        case 'ditolak':
            return 'badge-danger';
        case 'completed':
        case 'selesai':
            return 'badge-neutral';
        default:
            return 'badge-warning';
    }
};

export const formatDate = (dateString) => {
    if (!dateString || dateString === '-') return '-';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch (e) {
        return dateString;
    }
};

export const getTimelineSteps = (pendaftaran) => {
    if (!pendaftaran) return [];

    const rawStatus = (pendaftaran.status || 'pending').toLowerCase();
    const createdAt = formatDate(pendaftaran.created_at);
    const updatedAt = formatDate(pendaftaran.updated_at || pendaftaran.created_at);
    const isCompleted = rawStatus === 'completed' || rawStatus === 'selesai';

    const steps = [{
        label: 'Pengajuan Dibuat',
        date: createdAt,
        status: 'completed',
    }, {
        label: 'Verifikasi Admin',
        date: rawStatus === 'pending' ? 'Dalam proses' : updatedAt,
        status: rawStatus === 'pending' ? 'in_progress' : 'completed',
    }];

    if (rawStatus === 'rejected' || rawStatus === 'ditolak') {
        steps.push({ label: 'Ditolak', date: updatedAt, status: 'rejected' });
        return steps;
    }

    if (rawStatus === 'revision' || rawStatus === 'revisi') {
        steps.push({ label: 'Perlu Revisi', date: updatedAt, status: 'revision' });
        return steps;
    }

    if (rawStatus === 'accepted' || rawStatus === 'diterima' || isCompleted) {
        steps.push({ label: 'Diterima', date: updatedAt, status: 'completed' });
        steps.push({
            label: 'Selesai',
            date: isCompleted ? (pendaftaran.end_date ? formatDate(pendaftaran.end_date) : updatedAt) : '-',
            status: isCompleted ? 'completed' : 'pending',
        });
        return steps;
    }

    steps.push({ label: 'Diterima', date: '-', status: 'pending' });
    steps.push({ label: 'Selesai', date: '-', status: 'pending' });

    return steps;
};

export const isPastEndDate = (endDate) => {
    if (!endDate) return false;

    // Date-only string (Laravel 'date' cast -> 'YYYY-MM-DD'): parse as local day
    // so the whole end_date day still counts as active (endOfDay comparison).
    if (typeof endDate === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(endDate)) {
        const [y, m, d] = endDate.split('-').map(Number);
        const local = new Date(y, m - 1, d);
        local.setHours(23, 59, 59, 999);
        return local.getTime() < Date.now();
    }

    const end = new Date(endDate);
    if (isNaN(end.getTime())) return false;

    end.setHours(23, 59, 59, 999);
    return end.getTime() < Date.now();
};
