import React, {useState, useEffect} from 'react';
import {
  View,
  FlatList,
  StyleSheet,
  Alert,
  RefreshControl,
} from 'react-native';
import {
  Searchbar,
  FAB,
  Card,
  Title,
  Paragraph,
  Chip,
  Text,
  Badge,
  IconButton,
  Snackbar,
} from 'react-native-paper';
import {useMedicine} from '../context/MedicineContext';
import {Medicine, AlertType} from '../types/Medicine';

interface InventoryScreenProps {
  navigation: any;
}

const InventoryScreen: React.FC<InventoryScreenProps> = ({navigation}) => {
  const {
    state,
    deleteMedicine,
    checkForAlerts,
    searchMedicines,
    getLowStockMedicines,
    getExpiringMedicines,
  } = useMedicine();

  const [searchQuery, setSearchQuery] = useState('');
  const [filteredMedicines, setFilteredMedicines] = useState<Medicine[]>([]);
  const [selectedFilter, setSelectedFilter] = useState<'all' | 'low_stock' | 'expiring'>('all');
  const [snackbarVisible, setSnackbarVisible] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');

  useEffect(() => {
    checkForAlerts();
  }, [state.medicines]);

  useEffect(() => {
    filterMedicines();
  }, [searchQuery, selectedFilter, state.medicines]);

  const filterMedicines = () => {
    let medicines: Medicine[] = [];
    
    switch (selectedFilter) {
      case 'low_stock':
        medicines = getLowStockMedicines();
        break;
      case 'expiring':
        medicines = getExpiringMedicines(30);
        break;
      default:
        medicines = state.medicines;
    }

    if (searchQuery.trim()) {
      medicines = searchMedicines(searchQuery);
    }

    setFilteredMedicines(medicines);
  };

  const handleDeleteMedicine = (medicine: Medicine) => {
    Alert.alert(
      'Delete Medicine',
      `Are you sure you want to delete ${medicine.name}?`,
      [
        {text: 'Cancel', style: 'cancel'},
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            await deleteMedicine(medicine.id);
            setSnackbarMessage(`${medicine.name} deleted successfully`);
            setSnackbarVisible(true);
          },
        },
      ]
    );
  };

  const getStockStatusColor = (medicine: Medicine): string => {
    if (medicine.quantity === 0) return '#F44336';
    if (medicine.quantity <= medicine.minStockLevel) return '#FF9800';
    return '#4CAF50';
  };

  const getExpiryStatusColor = (expiryDate: Date): string => {
    const now = new Date();
    const daysUntilExpiry = Math.ceil((expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysUntilExpiry < 0) return '#F44336';
    if (daysUntilExpiry <= 30) return '#FF9800';
    return '#4CAF50';
  };

  const renderMedicineCard = ({item: medicine}: {item: Medicine}) => {
    const stockColor = getStockStatusColor(medicine);
    const expiryColor = getExpiryStatusColor(medicine.expiryDate);
    const isExpired = medicine.expiryDate < new Date();
    const daysUntilExpiry = Math.ceil((medicine.expiryDate.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));

    return (
      <Card style={styles.medicineCard}>
        <Card.Content>
          <View style={styles.cardHeader}>
            <View style={styles.medicineInfo}>
              <Title style={styles.medicineName}>{medicine.name}</Title>
              {medicine.genericName && (
                <Paragraph style={styles.genericName}>({medicine.genericName})</Paragraph>
              )}
              <Paragraph style={styles.manufacturer}>{medicine.manufacturer}</Paragraph>
            </View>
            <View style={styles.cardActions}>
              <IconButton
                icon="pencil"
                size={20}
                onPress={() => navigation.navigate('EditMedicine', {medicine})}
              />
              <IconButton
                icon="delete"
                size={20}
                iconColor="#F44336"
                onPress={() => handleDeleteMedicine(medicine)}
              />
            </View>
          </View>

          <View style={styles.detailsRow}>
            <View style={styles.stockInfo}>
              <Text style={styles.label}>Stock:</Text>
              <Badge style={[styles.badge, {backgroundColor: stockColor}]}>
                {medicine.quantity} units
              </Badge>
            </View>
            <View style={styles.priceInfo}>
              <Text style={styles.label}>Price:</Text>
              <Text style={styles.price}>${medicine.unitPrice.toFixed(2)}</Text>
            </View>
          </View>

          <View style={styles.detailsRow}>
            <View style={styles.categoryInfo}>
              <Chip mode="outlined" compact style={styles.categoryChip}>
                {medicine.category}
              </Chip>
              <Chip mode="outlined" compact style={styles.formChip}>
                {medicine.form}
              </Chip>
            </View>
          </View>

          <View style={styles.expiryRow}>
            <Text style={styles.label}>Expires:</Text>
            <Text style={[styles.expiryDate, {color: expiryColor}]}>
              {medicine.expiryDate.toDateString()}
              {isExpired && ' (EXPIRED)'}
              {!isExpired && daysUntilExpiry <= 30 && ` (${daysUntilExpiry} days)`}
            </Text>
          </View>

          <View style={styles.dosageRow}>
            <Text style={styles.label}>Dosage:</Text>
            <Text style={styles.dosage}>{medicine.dosage}</Text>
            {medicine.prescriptionRequired && (
              <Chip mode="outlined" compact style={styles.prescriptionChip}>
                Prescription Required
              </Chip>
            )}
          </View>
        </Card.Content>
      </Card>
    );
  };

  const unreadAlerts = state.alerts.filter(alert => !alert.isRead);

  return (
    <View style={styles.container}>
      <Searchbar
        placeholder="Search medicines..."
        onChangeText={setSearchQuery}
        value={searchQuery}
        style={styles.searchbar}
      />

      <View style={styles.filterRow}>
        <Chip
          mode={selectedFilter === 'all' ? 'flat' : 'outlined'}
          selected={selectedFilter === 'all'}
          onPress={() => setSelectedFilter('all')}
          style={styles.filterChip}>
          All ({state.medicines.length})
        </Chip>
        <Chip
          mode={selectedFilter === 'low_stock' ? 'flat' : 'outlined'}
          selected={selectedFilter === 'low_stock'}
          onPress={() => setSelectedFilter('low_stock')}
          style={styles.filterChip}>
          Low Stock ({getLowStockMedicines().length})
        </Chip>
        <Chip
          mode={selectedFilter === 'expiring' ? 'flat' : 'outlined'}
          selected={selectedFilter === 'expiring'}
          onPress={() => setSelectedFilter('expiring')}
          style={styles.filterChip}>
          Expiring ({getExpiringMedicines(30).length})
        </Chip>
      </View>

      {unreadAlerts.length > 0 && (
        <Card style={styles.alertCard}>
          <Card.Content>
            <Title style={styles.alertTitle}>
              ⚠️ {unreadAlerts.length} Alert{unreadAlerts.length > 1 ? 's' : ''}
            </Title>
            {unreadAlerts.slice(0, 3).map(alert => (
              <Paragraph key={alert.id} style={styles.alertMessage}>
                • {alert.message}
              </Paragraph>
            ))}
            {unreadAlerts.length > 3 && (
              <Paragraph style={styles.alertMore}>
                +{unreadAlerts.length - 3} more alerts
              </Paragraph>
            )}
          </Card.Content>
        </Card>
      )}

      <FlatList
        data={filteredMedicines}
        renderItem={renderMedicineCard}
        keyExtractor={item => item.id}
        style={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={state.loading}
            onRefresh={() => checkForAlerts()}
          />
        }
        ListEmptyComponent={
          <Card style={styles.emptyCard}>
            <Card.Content>
              <Title>No medicines found</Title>
              <Paragraph>
                {searchQuery ? 'Try adjusting your search' : 'Add some medicines to get started'}
              </Paragraph>
            </Card.Content>
          </Card>
        }
      />

      <FAB
        style={styles.fab}
        icon="plus"
        onPress={() => navigation.navigate('AddMedicine')}
      />

      <Snackbar
        visible={snackbarVisible}
        onDismiss={() => setSnackbarVisible(false)}
        duration={3000}>
        {snackbarMessage}
      </Snackbar>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  searchbar: {
    margin: 16,
    elevation: 4,
  },
  filterRow: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingBottom: 8,
    gap: 8,
  },
  filterChip: {
    flex: 1,
  },
  alertCard: {
    margin: 16,
    marginBottom: 8,
    backgroundColor: '#FFF3E0',
  },
  alertTitle: {
    color: '#E65100',
    fontSize: 16,
  },
  alertMessage: {
    color: '#BF360C',
    fontSize: 14,
  },
  alertMore: {
    color: '#BF360C',
    fontSize: 14,
    fontStyle: 'italic',
  },
  list: {
    flex: 1,
    paddingHorizontal: 16,
  },
  medicineCard: {
    marginBottom: 12,
    elevation: 2,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  medicineInfo: {
    flex: 1,
  },
  medicineName: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  genericName: {
    fontSize: 14,
    color: '#666',
    fontStyle: 'italic',
    marginBottom: 2,
  },
  manufacturer: {
    fontSize: 14,
    color: '#888',
  },
  cardActions: {
    flexDirection: 'row',
  },
  detailsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 12,
  },
  stockInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  priceInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  categoryInfo: {
    flexDirection: 'row',
    gap: 8,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
  },
  badge: {
    color: 'white',
  },
  price: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2E7D32',
  },
  categoryChip: {
    height: 28,
  },
  formChip: {
    height: 28,
  },
  expiryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
    gap: 8,
  },
  expiryDate: {
    fontSize: 14,
    fontWeight: '600',
  },
  dosageRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
    gap: 8,
  },
  dosage: {
    fontSize: 14,
    flex: 1,
  },
  prescriptionChip: {
    height: 28,
    backgroundColor: '#E3F2FD',
  },
  emptyCard: {
    margin: 32,
    padding: 16,
    alignItems: 'center',
  },
  fab: {
    position: 'absolute',
    margin: 16,
    right: 0,
    bottom: 0,
    backgroundColor: '#6200EE',
  },
});

export default InventoryScreen;