# Enterprise Dashboard Documentation

## Overview
The GoalDocs Enterprise Dashboard provides a comprehensive, real-time view of your document management system with advanced analytics, monitoring, and quick actions.

## Features

### 1. **Overview Statistics**
- **Total Files**: Complete count of files in the system
- **Storage Used**: Total storage consumption with formatted display
- **Active Users**: Number of users active in the last 7 days
- **Total Folders**: Count of all folders in the system

#### Growth Metrics
- Files uploaded this week
- Growth rate percentage (week-over-week)
- Storage trend analysis

### 2. **Quick Activity Cards**
Real-time metrics for today's activities:
- **Files Today**: Number of files uploaded today
- **Downloads Today**: Number of downloads performed today
- **Active Shares**: Currently active external shares
- **Pending Tasks**: Workflows awaiting approval

### 3. **Activity Analytics**

#### Activity Trend Chart
- Visual representation of activity over time
- Interactive area chart with smooth curves
- Drill-down capability for detailed analysis
- Time period filtering (7d, 30d, 90d, 1y)

#### Activity Distribution
- Donut chart showing activity breakdown by type
- Categories: Upload, Download, View, Share, Delete
- Real-time data updates
- Export capabilities

### 4. **Storage Analytics**

#### Storage by File Type
- Horizontal bar chart showing storage distribution
- Top 10 file types by storage usage
- MB/GB formatted values
- Optimization recommendations

#### Storage Trend
- 30-day storage growth visualization
- Daily storage accumulation tracking
- File count trends
- Predictive analytics

### 5. **Document Analytics**
- Most popular files (by activity count)
- File type distribution
- Upload trends
- Share statistics
- Version control metrics

### 6. **Workflow Management**
- Total workflows overview
- Active workflow count
- Completed workflows
- Pending approvals
- Quick action buttons

### 7. **Team Analytics** (Admin Only)
- Total team members
- Active members (last 7 days)
- Top contributors by file uploads
- Department statistics
- User engagement metrics

### 8. **System Health Monitor** (Admin Only)
Real-time system health indicators:
- **Storage Status**: Disk usage and capacity
- **API Status**: API endpoint health
- **Database Status**: Database connection and performance
- **Queue Status**: Background job processing

Status indicators:
- 🟢 **Healthy**: System operating normally
- 🟡 **Warning**: Attention required
- 🔴 **Critical**: Immediate action needed

### 9. **Security Overview** (Admin Only)
- Total security events
- Failed login attempts
- Suspicious activities detected
- Audit log count
- Recent security events list

### 10. **Recent Activities Feed**
- Real-time activity stream
- User actions with timestamps
- File context and links
- Activity type indicators
- Relative time display

### 11. **Quick Actions Panel**
One-click access to common operations:
- Upload File
- Create New Folder
- Search Files
- Generate Report

## User Permissions

### Regular Users
Regular users see personalized dashboard data:
- Only their own files and folders
- Their personal activity metrics
- Their own workflow tasks
- Personal storage usage

### Administrators
Administrators see organization-wide data:
- All files and folders across the system
- System-wide activity metrics
- All user workflows
- Complete storage analytics
- Team performance metrics
- System health indicators
- Security monitoring dashboard

## Data Refresh

### Automatic Refresh
- Overview statistics: Cached for 1 hour
- Activity data: Real-time updates
- Charts: Auto-refresh on period change

### Manual Refresh
- Click period filter buttons to update data
- Page reload for complete refresh
- API endpoint for programmatic updates

### API Endpoint
```
GET /api/dashboard/data?period=30d&section=overview
```

Parameters:
- `period`: 7d, 30d, 90d, 1y
- `section`: overview, activity, documents, all

## Performance Optimization

### Caching Strategy
- Overview stats: 1-hour cache
- Processing analytics: 1-hour cache
- Chart data: 30-minute cache
- Recent activities: No cache (real-time)

### Database Optimization
- Indexed queries for fast retrieval
- Aggregated data calculations
- Efficient joins and grouping
- Query result caching

## Customization

### Period Filtering
Users can filter dashboard data by time period:
- **7 Days**: Last week's activity
- **30 Days**: Last month (default)
- **90 Days**: Last quarter
- **1 Year**: Annual overview

### Chart Customization
Charts can be customized via ApexCharts options:
- Colors and themes
- Chart types (area, bar, donut)
- Data labels and tooltips
- Export formats (PNG, SVG, CSV)

## Technical Implementation

### Controller
`App\Http\Controllers\DashboardController`
- Main dashboard view
- Data aggregation methods
- Permission-based filtering
- API endpoints for AJAX

### Service Layer
`App\Services\AnalyticsService`
- Document usage analytics
- Processing analytics
- Storage analytics
- User activity tracking

### Views
`resources/views/dashboard/index.blade.php`
- Responsive grid layout
- Bootstrap 5 components
- ApexCharts integration
- Real-time updates

### Models Used
- File
- Folder
- User
- RecentActivity
- ExternalShare
- DocumentWorkflow
- SecurityLog
- AuditLog
- BatchJob
- OcrResult
- DocumentConversion

## Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Mobile Responsiveness
- Fully responsive design
- Touch-friendly interactions
- Optimized for tablets
- Progressive Web App ready

## Accessibility
- WCAG 2.1 Level AA compliant
- Screen reader compatible
- Keyboard navigation support
- High contrast mode

## Export Capabilities
- PDF reports
- Excel spreadsheets
- CSV data exports
- Chart image exports

## Future Enhancements
- AI-powered insights
- Predictive analytics
- Custom widget builder
- Advanced filtering options
- Real-time notifications
- Comparative analysis
- Custom date ranges
- Scheduled reports

## Support
For technical support or feature requests:
- Email: support@goaldocs.com
- Documentation: https://docs.goaldocs.com
- GitHub Issues: https://github.com/your-org/goaldocs/issues

---

**Last Updated**: October 2025
**Version**: 1.0.0
