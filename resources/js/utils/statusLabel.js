/**
 * Shared utility for Application Status mapping
 * Handles normalized status values:
 * - 'pending' / 'diajukan' -> "Dalam Proses" (badge-warning)
 * - 'accepted' / 'diterima' -> "Diterima" (badge-success)
 * - 'rejected' / 'ditolak' -> "Ditolak" (badge-danger)
 * - 'completed' / 'selesai' -> "Selesai" (badge-info)
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
            return 'badge-info';
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

    // Step 1: Pengajuan Dibuat (Always completed once application is created)
    const step1 = {
        label: 'Pengajuan Dibuat',
        date: createdAt,
        status: 'completed',
    };

    // If status is rejected
    if (rawStatus === 'rejected' || rawStatus === 'ditolak') {
        const step2 = {
            label: 'Ditolak',
            date: updatedAt,
            status: 'rejected',
        };
        return [step1, step2];
    }

    // If status is revision
    if (rawStatus === 'revision' || rawStatus === 'revisi') {
        const step2 = {
            label: 'Perlu Revisi',
            date: updatedAt,
            status: 'revision',
        };
        return [step1, step2];
    }

    // Step 2: Menunggu Verifikasi Admin OR Diterima
    let step2Status = 'in_progress';
    let step2Label = 'Menunggu Verifikasi Admin';
    let step2Date = 'Dalam proses';

    if (rawStatus === 'accepted' || rawStatus === 'diterima' || rawStatus === 'completed' || rawStatus === 'selesai') {
        step2Status = 'completed';
        step2Label = 'Diterima';
        step2Date = updatedAt;
    }

    const step2 = {
        label: step2Label,
        date: step2Date,
        status: step2Status,
    };

    // Step 3: Selesai
    let step3Status = 'pending';
    let step3Date = '-';

    if (rawStatus === 'completed' || rawStatus === 'selesai') {
        step3Status = 'completed';
        step3Date = updatedAt;
    }

    const step3 = {
        label: 'Selesai',
        date: step3Date,
        status: step3Status,
    };

    return [step1, step2, step3];
};
