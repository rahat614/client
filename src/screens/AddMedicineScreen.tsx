import React, {useState} from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Alert,
} from 'react-native';
import {
  TextInput,
  Button,
  Card,
  Title,
  Switch,
  Text,
  Snackbar,
  Menu,
  Divider,
} from 'react-native-paper';
import DatePicker from 'react-native-date-picker';
import {useMedicine} from '../context/MedicineContext';
import {MedicineCategory, MedicineForm} from '../types/Medicine';

interface AddMedicineScreenProps {
  navigation: any;
}

const AddMedicineScreen: React.FC<AddMedicineScreenProps> = ({navigation}) => {
  const {addMedicine} = useMedicine();

  // Form state
  const [formData, setFormData] = useState({
    name: '',
    genericName: '',
    manufacturer: '',
    batchNumber: '',
    expiryDate: new Date(new Date().setFullYear(new Date().getFullYear() + 2)),
    quantity: '',
    unitPrice: '',
    category: MedicineCategory.OTHER,
    minStockLevel: '',
    description: '',
    sideEffects: '',
    dosage: '',
    form: MedicineForm.TABLET,
    prescriptionRequired: false,
  });

  const [showDatePicker, setShowDatePicker] = useState(false);
  const [showCategoryMenu, setShowCategoryMenu] = useState(false);
  const [showFormMenu, setShowFormMenu] = useState(false);
  const [loading, setLoading] = useState(false);
  const [snackbarVisible, setSnackbarVisible] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');

  const updateFormData = (field: string, value: any) => {
    setFormData(prev => ({...prev, [field]: value}));
  };

  const validateForm = (): boolean => {
    const errors: string[] = [];

    if (!formData.name.trim()) errors.push('Medicine name is required');
    if (!formData.manufacturer.trim()) errors.push('Manufacturer is required');
    if (!formData.batchNumber.trim()) errors.push('Batch number is required');
    if (!formData.quantity.trim() || parseInt(formData.quantity) < 0) {
      errors.push('Valid quantity is required');
    }
    if (!formData.unitPrice.trim() || parseFloat(formData.unitPrice) < 0) {
      errors.push('Valid unit price is required');
    }
    if (!formData.minStockLevel.trim() || parseInt(formData.minStockLevel) < 0) {
      errors.push('Valid minimum stock level is required');
    }
    if (!formData.dosage.trim()) errors.push('Dosage information is required');

    if (errors.length > 0) {
      Alert.alert('Validation Error', errors.join('\n'));
      return false;
    }

    return true;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;

    setLoading(true);
    try {
      await addMedicine({
        name: formData.name.trim(),
        genericName: formData.genericName.trim() || undefined,
        manufacturer: formData.manufacturer.trim(),
        batchNumber: formData.batchNumber.trim(),
        expiryDate: formData.expiryDate,
        quantity: parseInt(formData.quantity),
        unitPrice: parseFloat(formData.unitPrice),
        category: formData.category,
        minStockLevel: parseInt(formData.minStockLevel),
        description: formData.description.trim() || undefined,
        sideEffects: formData.sideEffects.trim() || undefined,
        dosage: formData.dosage.trim(),
        form: formData.form,
        prescriptionRequired: formData.prescriptionRequired,
      });

      setSnackbarMessage('Medicine added successfully!');
      setSnackbarVisible(true);
      
      // Navigate back after a short delay
      setTimeout(() => {
        navigation.goBack();
      }, 1500);
    } catch (error) {
      Alert.alert('Error', 'Failed to add medicine. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Basic Information</Title>
          
          <TextInput
            label="Medicine Name *"
            value={formData.name}
            onChangeText={(text) => updateFormData('name', text)}
            style={styles.input}
            mode="outlined"
          />

          <TextInput
            label="Generic Name"
            value={formData.genericName}
            onChangeText={(text) => updateFormData('genericName', text)}
            style={styles.input}
            mode="outlined"
          />

          <TextInput
            label="Manufacturer *"
            value={formData.manufacturer}
            onChangeText={(text) => updateFormData('manufacturer', text)}
            style={styles.input}
            mode="outlined"
          />

          <TextInput
            label="Batch Number *"
            value={formData.batchNumber}
            onChangeText={(text) => updateFormData('batchNumber', text)}
            style={styles.input}
            mode="outlined"
          />
        </Card.Content>
      </Card>

      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Stock & Pricing</Title>
          
          <View style={styles.row}>
            <TextInput
              label="Quantity *"
              value={formData.quantity}
              onChangeText={(text) => updateFormData('quantity', text)}
              style={[styles.input, styles.halfWidth]}
              mode="outlined"
              keyboardType="numeric"
            />

            <TextInput
              label="Unit Price ($) *"
              value={formData.unitPrice}
              onChangeText={(text) => updateFormData('unitPrice', text)}
              style={[styles.input, styles.halfWidth]}
              mode="outlined"
              keyboardType="decimal-pad"
            />
          </View>

          <TextInput
            label="Minimum Stock Level *"
            value={formData.minStockLevel}
            onChangeText={(text) => updateFormData('minStockLevel', text)}
            style={styles.input}
            mode="outlined"
            keyboardType="numeric"
            placeholder="Alert when stock falls below this level"
          />

          <View style={styles.dateRow}>
            <Text style={styles.dateLabel}>Expiry Date *</Text>
            <Button
              mode="outlined"
              onPress={() => setShowDatePicker(true)}
              style={styles.dateButton}>
              {formData.expiryDate.toDateString()}
            </Button>
          </View>
        </Card.Content>
      </Card>

      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Category & Form</Title>
          
          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.menuLabel}>Category *</Text>
              <Menu
                visible={showCategoryMenu}
                onDismiss={() => setShowCategoryMenu(false)}
                anchor={
                  <Button
                    mode="outlined"
                    onPress={() => setShowCategoryMenu(true)}
                    contentStyle={styles.menuButton}>
                    {formData.category}
                  </Button>
                }>
                {Object.values(MedicineCategory).map((category) => (
                  <Menu.Item
                    key={category}
                    onPress={() => {
                      updateFormData('category', category);
                      setShowCategoryMenu(false);
                    }}
                    title={category}
                  />
                ))}
              </Menu>
            </View>

            <View style={styles.halfWidth}>
              <Text style={styles.menuLabel}>Form *</Text>
              <Menu
                visible={showFormMenu}
                onDismiss={() => setShowFormMenu(false)}
                anchor={
                  <Button
                    mode="outlined"
                    onPress={() => setShowFormMenu(true)}
                    contentStyle={styles.menuButton}>
                    {formData.form}
                  </Button>
                }>
                {Object.values(MedicineForm).map((form) => (
                  <Menu.Item
                    key={form}
                    onPress={() => {
                      updateFormData('form', form);
                      setShowFormMenu(false);
                    }}
                    title={form}
                  />
                ))}
              </Menu>
            </View>
          </View>

          <TextInput
            label="Dosage *"
            value={formData.dosage}
            onChangeText={(text) => updateFormData('dosage', text)}
            style={styles.input}
            mode="outlined"
            placeholder="e.g., 500mg twice daily"
          />

          <View style={styles.switchRow}>
            <Text style={styles.switchLabel}>Prescription Required</Text>
            <Switch
              value={formData.prescriptionRequired}
              onValueChange={(value) => updateFormData('prescriptionRequired', value)}
            />
          </View>
        </Card.Content>
      </Card>

      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Additional Information</Title>
          
          <TextInput
            label="Description"
            value={formData.description}
            onChangeText={(text) => updateFormData('description', text)}
            style={styles.input}
            mode="outlined"
            multiline
            numberOfLines={3}
            placeholder="Brief description of the medicine"
          />

          <TextInput
            label="Side Effects"
            value={formData.sideEffects}
            onChangeText={(text) => updateFormData('sideEffects', text)}
            style={styles.input}
            mode="outlined"
            multiline
            numberOfLines={3}
            placeholder="Known side effects and precautions"
          />
        </Card.Content>
      </Card>

      <View style={styles.buttonContainer}>
        <Button
          mode="outlined"
          onPress={() => navigation.goBack()}
          style={[styles.button, styles.cancelButton]}
          disabled={loading}>
          Cancel
        </Button>
        
        <Button
          mode="contained"
          onPress={handleSubmit}
          style={[styles.button, styles.submitButton]}
          loading={loading}
          disabled={loading}>
          Add Medicine
        </Button>
      </View>

      <DatePicker
        modal
        open={showDatePicker}
        date={formData.expiryDate}
        mode="date"
        minimumDate={new Date()}
        onConfirm={(date) => {
          setShowDatePicker(false);
          updateFormData('expiryDate', date);
        }}
        onCancel={() => setShowDatePicker(false)}
      />

      <Snackbar
        visible={snackbarVisible}
        onDismiss={() => setSnackbarVisible(false)}
        duration={3000}>
        {snackbarMessage}
      </Snackbar>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  card: {
    margin: 16,
    marginBottom: 8,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 16,
    color: '#6200EE',
  },
  input: {
    marginBottom: 12,
  },
  row: {
    flexDirection: 'row',
    gap: 12,
  },
  halfWidth: {
    flex: 1,
  },
  dateRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 8,
  },
  dateLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  dateButton: {
    flex: 1,
    marginLeft: 16,
  },
  menuLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  menuButton: {
    justifyContent: 'flex-start',
  },
  switchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 16,
    paddingVertical: 8,
  },
  switchLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 12,
    padding: 16,
    marginBottom: 32,
  },
  button: {
    flex: 1,
    paddingVertical: 8,
  },
  cancelButton: {
    borderColor: '#666',
  },
  submitButton: {
    backgroundColor: '#6200EE',
  },
});

export default AddMedicineScreen;