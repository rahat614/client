# Medicine Stock Management Mobile App

A comprehensive React Native mobile application for managing medicine inventory, tracking expiry dates, monitoring stock levels, and generating reports.

## Features

### 📱 Core Functionality
- **Medicine Inventory Management**: Add, edit, and delete medicines with detailed information
- **Stock Level Monitoring**: Track quantities and set minimum stock alerts
- **Expiry Date Tracking**: Monitor expiration dates with automated alerts
- **Search & Filter**: Find medicines quickly by name, category, or manufacturer
- **Real-time Alerts**: Get notified about low stock and expiring medicines

### 📊 Analytics & Reports
- **Visual Charts**: Stock trends, category distribution, and status overview
- **Top Medicines**: View highest value and quantity medicines
- **Transaction History**: Track all stock movements and changes
- **Export Data**: Share and backup medicine data

### 🔧 Data Management
- **Local Storage**: All data stored securely on device using AsyncStorage
- **Backup & Restore**: Create and restore local backups
- **Data Export**: Export data for sharing or external backup
- **Offline Support**: Works completely offline

## Technology Stack

- **Framework**: React Native 0.73.0
- **UI Library**: React Native Paper (Material Design)
- **Navigation**: React Navigation 6
- **State Management**: React Context + useReducer
- **Storage**: AsyncStorage
- **Charts**: React Native Chart Kit
- **TypeScript**: Full type safety
- **Date Picker**: React Native Date Picker

## Installation

### Prerequisites
- Node.js (>=16)
- React Native CLI
- Android Studio (for Android)
- Xcode (for iOS)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd medicine-stock-manager
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Install iOS dependencies** (iOS only)
   ```bash
   cd ios && pod install && cd ..
   ```

4. **Run the application**
   ```bash
   # For Android
   npm run android
   
   # For iOS
   npm run ios
   ```

## App Structure

```
src/
├── App.tsx                 # Main app component with navigation
├── context/
│   └── MedicineContext.tsx # Global state management
├── types/
│   └── Medicine.ts         # TypeScript interfaces and enums
├── screens/
│   ├── InventoryScreen.tsx # Main inventory listing
│   ├── AddMedicineScreen.tsx # Add new medicine form
│   ├── EditMedicineScreen.tsx # Edit medicine form
│   ├── ReportsScreen.tsx   # Analytics and reports
│   └── SettingsScreen.tsx  # App settings and data management
└── components/             # Reusable components (if needed)
```

## Key Features Explained

### Medicine Data Model
Each medicine contains:
- Basic info (name, generic name, manufacturer, batch number)
- Stock details (quantity, unit price, minimum stock level)
- Medical info (category, form, dosage, prescription requirement)
- Dates (expiry date, created/updated timestamps)
- Additional info (description, side effects)

### Alert System
The app automatically generates alerts for:
- **Low Stock**: When quantity falls below minimum level
- **Expired**: Medicines past expiry date
- **Expiring Soon**: Medicines expiring within 30 days

### Reports & Analytics
- Total inventory value calculation
- Category-wise medicine distribution
- Stock status overview (normal/low/expiring)
- 7-day stock movement trends
- Top medicines by value and quantity

### Data Management
- **AsyncStorage**: Persistent local storage
- **JSON Export**: Share data via platform share sheet
- **Backup System**: Local backup and restore functionality
- **Data Validation**: Form validation and error handling

## Usage Guide

### Adding a Medicine
1. Navigate to Inventory tab
2. Tap the + (plus) button
3. Fill in required fields (marked with *)
4. Set expiry date and minimum stock level
5. Save to add to inventory

### Managing Stock
- **View Status**: Color-coded stock levels (green=normal, orange=low, red=expired)
- **Edit Medicine**: Tap pencil icon on any medicine card
- **Delete Medicine**: Tap delete icon with confirmation
- **Search**: Use search bar to find specific medicines
- **Filter**: Use filter chips to view by status

### Viewing Reports
1. Navigate to Reports tab
2. View summary cards for quick stats
3. Scroll down for detailed charts and lists
4. Monitor alerts and trends

### Settings & Data
1. Navigate to Settings tab
2. Configure notifications and alerts
3. Export data for backup
4. Manage local storage

## Development

### Project Setup for Development
```bash
# Install dependencies
npm install

# Start Metro bundler
npm start

# Run on Android (in another terminal)
npm run android

# Run on iOS (in another terminal)
npm run ios
```

### Building for Production

#### Android
```bash
cd android
./gradlew assembleRelease
```

#### iOS
1. Open `ios/MedicineStockManager.xcworkspace` in Xcode
2. Select "Any iOS Device" as target
3. Product → Archive
4. Follow App Store deployment process

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue on the GitHub repository or contact the development team.

---

**Note**: This app is designed for personal medicine inventory management. Always consult healthcare professionals for medical advice and prescription management.
