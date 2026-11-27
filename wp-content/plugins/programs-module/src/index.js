// Source entry for the @wordpress/scripts build. The runtime admin.js is kept
// in admin/ so the plugin also works in a pre-built submission.
import { createRoot } from '@wordpress/element';
export function mountProgramEditor( element, component ) { createRoot( element ).render( component ); }
