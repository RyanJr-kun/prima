import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin, { Draggable } from '@fullcalendar/interaction';

// --- AKTIFKAN FITUR PREMIUM ---
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';

try {
  window.fullcalendar = {
    Calendar: Calendar,
    Draggable: Draggable,
    plugins: [
        dayGridPlugin, 
        timeGridPlugin, 
        listPlugin, 
        interactionPlugin,
        
        // --- MASUKKAN PLUGIN KE SINI ---
        resourceTimelinePlugin, 
        resourceTimeGridPlugin
    ]
  };
} catch (e) {
  console.error('FullCalendar init error:', e);
}

export { Calendar };